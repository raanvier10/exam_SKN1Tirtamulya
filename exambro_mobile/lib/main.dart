import 'package:flutter/material.dart';
import 'screens/splash_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ExambroApp());
}

class ExambroApp extends StatelessWidget {
  const ExambroApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'ExaSatria',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: const SplashScreen(),
    );
  }
}
