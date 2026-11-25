# Preserve Flutter & native MethodChannel entrypoints
-keep class com.example.exambro_mobile.** { *; }
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.plugins.** { *; }

# Preserve Geolocator & Google Play Services location models/callbacks
-keep class com.baseflow.geolocator.** { *; }
-keep class com.google.android.gms.location.** { *; }

# Preserve WebView JavascriptInterface & callbacks
-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}
