package com.example.exambro_mobile

import android.content.ComponentCallbacks2
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.os.Handler
import android.os.Looper
import android.os.SystemClock
import android.view.WindowManager
import android.webkit.CookieManager
import java.io.File
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity: FlutterActivity() {
    private val CHANNEL = "exam.bro/secure"
    private var channel: MethodChannel? = null
    private var isSecured = false
    private var isSecuringSession = false
    private var awaitingInitialFocus = false
    private var isActivityPaused = false
    private val focusHandler = Handler(Looper.getMainLooper())
    private var pendingFloatingRunnable: Runnable? = null
    private var pendingSecureRunnable: Runnable? = null
    private var screenReceiver: BroadcastReceiver? = null
    private var screenTurnedOff = false
    private var lastCookieFlushMs: Long = 0L

    companion object {
        private const val INITIAL_SECURE_DELAY_MS = 5000L
        private const val FOCUS_REGAIN_ARM_DELAY_MS = 800L
        private const val FLOATING_WINDOW_CONFIRM_MS = 1500L
    }

    private fun flushCookiesThrottled(force: Boolean = false) {
        val now = SystemClock.elapsedRealtime()
        if (!force && (now - lastCookieFlushMs) < 1500L) return
        lastCookieFlushMs = now
        try {
            CookieManager.getInstance().flush()
        } catch (e: Exception) {
            e.printStackTrace()
        }
    }

    private fun clearTempCacheDir(dir: File?) {
        if (dir == null || !dir.exists()) return
        val children = dir.listFiles() ?: return
        for (child in children) {
            val name = child.name.lowercase()
            // Biarkan Chromium WebView mengelola & membersihkan cache miliknya sendiri lewat WebView.clearCache(true)
            // agar tidak terjadi race condition file index saat halaman baru ditutup.
            if (name == "webview" || name.contains("chromium")) {
                continue
            }
            if (child.isDirectory) {
                clearTempCacheDir(child)
            } else {
                child.delete()
            }
        }
    }

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
                isSecured = false
                isSecuringSession = true
                awaitingInitialFocus = false
                screenTurnedOff = false
                pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
                pendingFloatingRunnable = null
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

                // Beri jeda startup agar dialog sistem Android (Sematkan Aplikasi / Immersive) dan inisialisasi WebView
                // pada perangkat RAM kecil tidak langsung dianggap pelanggaran, dan pastikan fokus jendela sudah didapat dulu.
                pendingSecureRunnable?.let { focusHandler.removeCallbacks(it) }
                val secureRunnable = Runnable {
                    if (!isSecuringSession) return@Runnable
                    if (!isActivityPaused && hasWindowFocus()) {
                        isSecured = true
                        awaitingInitialFocus = false
                    } else {
                        awaitingInitialFocus = true
                    }
                }
                pendingSecureRunnable = secureRunnable
                focusHandler.postDelayed(secureRunnable, INITIAL_SECURE_DELAY_MS)
                result.success(true)
            } else if (call.method == "clearSecureScreen") {
                isSecuringSession = false
                awaitingInitialFocus = false
                isSecured = false
                pendingSecureRunnable?.let { focusHandler.removeCallbacks(it) }
                pendingSecureRunnable = null
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
                // Paksa penyimpanan cookie akun Google ke penyimpanan fisik (disk) dengan opsi force atau throttling I/O
                val force = (call.arguments as? Boolean) ?: false
                flushCookiesThrottled(force = force)
                result.success(true)
            } else if (call.method == "clearWebCache") {
                // Bersihkan file cache sementara (gambar/aset) di internal storage tanpa menghapus cookie Google
                try {
                    clearTempCacheDir(applicationContext.cacheDir)
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(true)
            } else if (call.method == "hasGoogleCookies") {
                var hasAuthCookie = false
                try {
                    val cookies = CookieManager.getInstance().getCookie("https://accounts.google.com") ?: ""
                    hasAuthCookie = cookies.contains("SID=") ||
                        cookies.contains("LSID=") ||
                        cookies.contains("SAPISID=") ||
                        cookies.contains("__Secure-1PSID=") ||
                        cookies.contains("__Secure-3PSID=")
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(hasAuthCookie)
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
        flushCookiesThrottled(force = true)
    }

    override fun onTrimMemory(level: Int) {
        super.onTrimMemory(level)
        if (level >= ComponentCallbacks2.TRIM_MEMORY_RUNNING_LOW) {
            flushCookiesThrottled(force = level >= ComponentCallbacks2.TRIM_MEMORY_UI_HIDDEN)
            channel?.invokeMethod("onLowMemory", level)
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
        armSecurityIfFocusedAfterGrace()
    }

    override fun onDestroy() {
        isSecuringSession = false
        awaitingInitialFocus = false
        isSecured = false
        pendingSecureRunnable?.let { focusHandler.removeCallbacks(it) }
        pendingSecureRunnable = null
        pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
        pendingFloatingRunnable = null
        focusHandler.removeCallbacksAndMessages(null)
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
        flushCookiesThrottled(force = true)
        channel?.setMethodCallHandler(null)
        channel = null
        super.onDestroy()
    }

    override fun onMultiWindowModeChanged(isInMultiWindowMode: Boolean) {
        super.onMultiWindowModeChanged(isInMultiWindowMode)
        if (isInMultiWindowMode && isSecured) {
            channel?.invokeMethod("onWindowFocusLost", "MULTI_WINDOW")
        }
    }

    private fun armSecurityIfFocusedAfterGrace() {
        if (isSecuringSession && !isSecured && awaitingInitialFocus && !isActivityPaused && hasWindowFocus()) {
            awaitingInitialFocus = false
            pendingSecureRunnable?.let { focusHandler.removeCallbacks(it) }
            val armRunnable = Runnable {
                if (!isSecuringSession) return@Runnable
                isSecured = true
                if (!isActivityPaused && !hasWindowFocus()) {
                    scheduleFloatingWindowCheck()
                }
            }
            pendingSecureRunnable = armRunnable
            focusHandler.postDelayed(armRunnable, FOCUS_REGAIN_ARM_DELAY_MS)
        }
    }

    private fun scheduleFloatingWindowCheck() {
        pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
        val runnable = Runnable {
            if (!isActivityPaused && !hasWindowFocus() && isSecured) {
                channel?.invokeMethod("onWindowFocusLost", "FLOATING_WINDOW")
            }
        }
        pendingFloatingRunnable = runnable
        focusHandler.postDelayed(runnable, FLOATING_WINDOW_CONFIRM_MS)
    }

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (!hasFocus) {
            if (isSecured && !isActivityPaused) {
                // Tunda konfirmasi kehilangan fokus agar glitch relayout WebView / sistem bar pada RAM kecil
                // tidak dianggap Floating Window, sedangkan jendela mengambang nyata tetap terdeteksi.
                scheduleFloatingWindowCheck()
            }
        } else {
            // Fokus layar kembali, bersihkan timer floating window
            pendingFloatingRunnable?.let { focusHandler.removeCallbacks(it) }
            pendingFloatingRunnable = null
            armSecurityIfFocusedAfterGrace()
        }
    }
}
