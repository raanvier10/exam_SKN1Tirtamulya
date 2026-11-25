// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:exambro_mobile/api_service.dart';
import 'package:exambro_mobile/screens/login_screen.dart';

void main() {
  testWidgets('Smoke test for Login Screen', (WidgetTester tester) async {
    await tester.pumpWidget(const MaterialApp(home: LoginScreen()));
    expect(find.text('Selamat Datang'), findsOneWidget);
  });

  test('Local exam draft save, read, and clear work cleanly', () async {
    SharedPreferences.setMockInitialValues({});
    await ApiService.saveExamDraft(99, '{"field_0":"jawaban A"}');
    expect(await ApiService.getExamDraft(99), '{"field_0":"jawaban A"}');
    await ApiService.clearExamDraft(99);
    expect(await ApiService.getExamDraft(99), isNull);
  });
}
