package com.example.exambro_mobile

import android.view.WindowManager
import io.flutter.embedding.android.FlutterActivity
import io.flutter.embedding.engine.FlutterEngine
import io.flutter.plugin.common.MethodChannel

class MainActivity: FlutterActivity() {
    private val CHANNEL = "exam.bro/secure"
    private var channel: MethodChannel? = null
    private var isSecured = false

    override fun configureFlutterEngine(flutterEngine: FlutterEngine) {
        super.configureFlutterEngine(flutterEngine)
        channel = MethodChannel(flutterEngine.dartExecutor.binaryMessenger, CHANNEL)
        channel?.setMethodCallHandler { call, result ->
            if (call.method == "secureScreen") {
                isSecured = true
                window.addFlags(WindowManager.LayoutParams.FLAG_SECURE)
                try {
                    startLockTask()
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(true)
            } else if (call.method == "clearSecureScreen") {
                isSecured = false
                window.clearFlags(WindowManager.LayoutParams.FLAG_SECURE)
                try {
                    stopLockTask()
                } catch (e: Exception) {
                    e.printStackTrace()
                }
                result.success(true)
            } else {
                result.notImplemented()
            }
        }
    }

    override fun onMultiWindowModeChanged(isInMultiWindowMode: Boolean) {
        super.onMultiWindowModeChanged(isInMultiWindowMode)
        if (isInMultiWindowMode && isSecured) {
            channel?.invokeMethod("onWindowFocusLost", "MULTI_WINDOW")
        }
    }

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (!hasFocus && isSecured) {
            channel?.invokeMethod("onWindowFocusLost", "FLOATING_WINDOW")
        }
    }
}
