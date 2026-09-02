import 'package:flutter/material.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';
import 'api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Check if token exists to skip login
  final token = await ApiService.getToken();
  final Widget initialScreen = token != null ? const DashboardScreen() : const LoginScreen();

  runApp(ExambroApp(initialScreen: initialScreen));
}

class ExambroApp extends StatelessWidget {
  final Widget initialScreen;

  const ExambroApp({super.key, required this.initialScreen});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Exambro Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.blue,
        visualDensity: VisualDensity.adaptivePlatformDensity,
      ),
      home: initialScreen,
    );
  }
}
