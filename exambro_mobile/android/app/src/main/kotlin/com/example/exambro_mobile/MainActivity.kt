package com.example.exambro_mobile

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.os.Handler
import android.os.Looper
import android.view.WindowManager
import android.webkit.CookieManager
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity: FlutterActivity() {
    private val CHANNEL = "exam.bro/secure"
    private var channel: MethodChannel? = null
    private var isSecured = false
    private var isActivityPaused = false
    private val focusHandler = Handler(Looper.getMainLooper())
    private var pendingFloatingRunnable: Runnable? = null
    private var screenReceiver: BroadcastReceiver? = null
    private var screenTurnedOff = false

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)

        // Pastikan cookie WebView (termasuk Google Auth) diizinkan dan aktif
        try {
            CookieManager.getInstance().setAcceptCookie(true)
            CookieManager.getInstance().flush()
        } catch (e: Exception) {
            e.printStackTrace()
        }

        channel = MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL)
        channel?.setMethodCallHandler { call, result ->
            if (call.method == "secureScreen") {
                window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
                try {
                    startLockTask()
                } catch (e: Exception) {
                    e.printStackTrace()
                }

                // Daftarkan receiver deteksi layar HP dimatikan (Power button / Screen Off)
                if (screenReceiver == null) {
                    val filter = IntentFilter().apply {
                        addAction(Intent.ACTION_SCREEN_OFF)
                        addAction(Intent.ACTION_SCREEN_ON)
                        addAction(Intent.ACTION_USER_PRESENT)
                    }
                    screenReceiver = object : BroadcastReceiver() {
                        override fun onReceive(context: Context?, intent: Intent?) {
                            when (intent?.action) {
                                Intent.ACTION_SCREEN_OFF -> {
                                    screenTurnedOff = true
                                }
                                Intent.ACTION_USER_PRESENT, Intent.ACTION_SCREEN_ON -> {
                                    if (screenTurnedOff) {
                                        screenTurnedOff = false
                                        focusHandler.post {
                                            channel?.invokeMethod("onScreenOff", "SCREEN_OFF")
                                        }
                                    }
                                }
                            }
                        }
                    }
                    registerReceiver(screenReceiver, filter)
                }

                // Beri jeda 4 detik agar dialog sistem Android (Sematkan Aplikasi / Immersive) tidak dianggap pelanggaran
                focusHandler.postDelayed({
                    isSecured = true
                }, 4000)
                result.success(true)
            } else if (call.method == "clearSecureScreen") {
                isSecured = false
                screenReceiver?.let {
                    try {
                        unregisterReceiver(it)
                    } catch (e: Exception) {
                        e.printStackTrace()
                    }
                }
                screenReceiver = null
                screenTurnedOff = false
                pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
                pendingFloatingRunnable = null
                window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                try {
                    stopLockTask()
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(true)
            } else if (call.method == "flushCookies") {
                // Paksa penyimpanan cookie akun Google ke penyimpanan fisik (disk) agar tidak hilang saat app ditutup
                try {
                    CookieManager.getInstance().flush()
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(true)
            } else if (call.method == "clearCookies") {
                // Hapus seluruh cookie & storage WebView saat siswa logout agar tidak tertukar saat HP dipinjamkan
                try {
                    val cookieManager = CookieManager.getInstance()
                    cookieManager.removeAllCookies(null)
                    cookieManager.flush()
                    android.webkit.WebStorage.getInstance().deleteAllData()
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(true)
            } else {
                result.notImplemented()
            }
        }
    }

    override fun onPause() {
        super.onPause()
        isActivityPaused = true
        // Batalkan pending floating window: jika aplikasi di-minimize/pindah app,
        // ini adalah APP_SWITCH yang akan ditangani secara akurat oleh lifecycle Flutter (didChangeAppLifecycleState)
        pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
        pendingFloatingRunnable = null

        // Simpan sesi cookie saat aplikasi di-minimize
        try {
            CookieManager.getInstance().flush()
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }

    override fun onResume() {
        super.onResume()
        isActivityPaused = false
        if (screenTurnedOff) {
            screenTurnedOff = false
            focusHandler.post {
                channel?.invokeMethod("onScreenOff", "SCREEN_OFF")
            }
        }
    }

    override fun onDestroy() {
        screenReceiver?.let {
            try {
                unregisterReceiver(it)
            } catch (e: Exception) {
                e.printStackTrace()
            }
        }
        screenReceiver = null
        screenTurnedOff = false
        // Simpan sesi cookie saat aplikasi ditutup
        try {
            CookieManager.getInstance().flush()
        } catch (e: Exception) {
            e.printStackTrace()
        }
        super.onDestroy()
    }

    override fun onMultiWindowModeChanged(isInMultiWindowMode: Boolean) {
        super.onMultiWindowModeChanged(isInMultiWindowMode)
        if (isInMultiWindowMode && isSecured) {
            channel?.invokeMethod("onWindowFocusLost", "MULTI_WINDOW")
        }
    }

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (!hasFocus) {
            if (isSecured && !isActivityPaused) {
                // Tunda 500ms: jika activity mengalami pause dalam 500ms, itu adalah APP_SWITCH (bukan floating window).
                // Namun jika setelah 500ms activity tetap RESUMED/foreground tapi jendela tidak memiliki fokus,
                // berarti ada jendela mengambang aktif di atas layar (Floating Window / Smart Sidebar / Pop-up).
                pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
                val runnable = Runnable {
                    if (!isActivityPaused && !hasWindowFocus() && isSecured) {
                        channel?.invokeMethod("onWindowFocusLost", "FLOATING_WINDOW")
                    }
                }
                pendingFloatingRunnable = runnable
                focusHandler.postDelayed(runnable, 500)
            }
        } else {
            // Fokus layar kembali, bersihkan timer
            pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
            pendingFloatingRunnable = null
        }
    }
}
