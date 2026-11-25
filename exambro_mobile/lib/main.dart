import 'package:flutter/material.dart';
import 'screens/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  // Batasi cache bitmap native agar RAM 2GB-3GB tetap lega untuk WebView ujian
  PaintingBinding.instance.imageCache.maximumSize = 50;
  PaintingBinding.instance.imageCache.maximumSizeBytes = 25 << 20;
  runApp(const ExambroApp());
}

class ExambroApp extends StatelessWidget {
  const ExambroApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ExaSatrya',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: const SplashScreen(),
    );
  }
}
