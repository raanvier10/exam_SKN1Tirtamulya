import Flutter
import UIKit

@main
@objc class AppDelegate: FlutterAppDelegate, FlutterImplicitEngineDelegate {
  private var secureChannel: FlutterMethodChannel?
  private var isSecured = false

  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }

  func didInitializeImplicitFlutterEngine(_ engineBridge: FlutterImplicitEngineBridge) {
    GeneratedPluginRegistrant.register(with: engineBridge.pluginRegistry)

    let registrar = engineBridge.pluginRegistry.registrar(forPlugin: "ExamBroSecure")
    let channel = FlutterMethodChannel(name: "exam.bro/secure", binaryMessenger: registrar.messenger())
    self.secureChannel = channel

    channel.setMethodCallHandler { [weak self] (call: FlutterMethodCall, result: @escaping FlutterResult) in
      guard let self = self else { return }
      if call.method == "secureScreen" {
        self.isSecured = true
        NotificationCenter.default.removeObserver(self, name: UIScreen.capturedDidChangeNotification, object: nil)
        NotificationCenter.default.addObserver(self, selector: #selector(self.screenCaptureChanged), name: UIScreen.capturedDidChangeNotification, object: nil)
        result(true)
      } else if call.method == "clearSecureScreen" {
        self.isSecured = false
        NotificationCenter.default.removeObserver(self, name: UIScreen.capturedDidChangeNotification, object: nil)
        result(true)
      } else {
        result(FlutterMethodNotImplemented)
      }
    }
  }

  @objc private func screenCaptureChanged() {
    if isSecured && UIScreen.main.isCaptured {
      secureChannel?.invokeMethod("onWindowFocusLost", "SCREEN_RECORDING")
    }
  }
}
