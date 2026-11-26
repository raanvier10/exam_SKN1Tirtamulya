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
import 'package:exambro_mobile/screens/exam_screen.dart';
import 'package:exambro_mobile/screens/login_screen.dart';

void main() {
  testWidgets('Smoke test for Login Screen with 5-char verification', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(360, 640);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(const MaterialApp(home: LoginScreen()));
    expect(find.text('Selamat Datang'), findsOneWidget);
    expect(find.text('Kode Verifikasi'), findsOneWidget);
    expect(find.text('Ganti'), findsOneWidget);
    expect(find.widgetWithText(TextFormField, 'Masukkan 5 karakter'), findsOneWidget);

    // Isi NIS & Password lalu tekan Masuk tanpa kode verifikasi
    await tester.enterText(
      find.widgetWithText(TextFormField, 'Masukkan NIS atau Username'),
      '12345678',
    );
    await tester.enterText(
      find.widgetWithText(TextFormField, 'Masukkan kata sandi'),
      'password123',
    );
    await tester.ensureVisible(find.text('Masuk Sekarang'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Masuk Sekarang'));
    await tester.pumpAndSettle();
    expect(find.text('Kode verifikasi wajib diisi.'), findsOneWidget);

    // Isi kurang dari 5 karakter
    await tester.ensureVisible(find.widgetWithText(TextFormField, 'Masukkan 5 karakter'));
    await tester.pumpAndSettle();
    await tester.enterText(
      find.widgetWithText(TextFormField, 'Masukkan 5 karakter'),
      'abc',
    );
    await tester.pump();
    // UpperCase formatter mengubah input menjadi huruf kapital
    expect(find.text('ABC'), findsOneWidget);
    await tester.ensureVisible(find.text('Masuk Sekarang'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Masuk Sekarang'));
    await tester.pumpAndSettle();
    expect(find.text('Kode verifikasi harus 5 karakter.'), findsOneWidget);

    // Tombol Ganti mengosongkan input verifikasi
    await tester.ensureVisible(find.text('Ganti'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Ganti'));
    await tester.pump();
    expect(find.text('ABC'), findsNothing);
  });

  group('LoginScreen verification helpers', () {
    test('generateCaptchaCode always generates 5 valid characters', () {
      for (int i = 0; i < 20; i++) {
        final code = LoginScreen.generateCaptchaCode();
        expect(code.length, 5);
        for (final ch in code.split('')) {
          expect(LoginScreen.captchaCharset.contains(ch), isTrue);
        }
      }
    });

    test('validateCaptchaInput enforces required, length 5, and case-insensitive match', () {
      expect(LoginScreen.validateCaptchaInput('', 'ABC23'), 'Kode verifikasi wajib diisi.');
      expect(LoginScreen.validateCaptchaInput('ABCD', 'ABC23'), 'Kode verifikasi harus 5 karakter.');
      expect(
        LoginScreen.validateCaptchaInput('WRONG', 'ABC23'),
        'Kode verifikasi tidak sesuai. Silakan coba lagi.',
      );
      expect(LoginScreen.validateCaptchaInput('abc23', 'ABC23'), isNull);
    });
  });

  test('Local exam draft save, read, and clear work cleanly', () async {
    SharedPreferences.setMockInitialValues({});
    await ApiService.saveExamDraft(99, '{"field_0":"jawaban A"}');
    expect(await ApiService.getExamDraft(99), '{"field_0":"jawaban A"}');
    await ApiService.clearExamDraft(99);
    expect(await ApiService.getExamDraft(99), isNull);
  });

  group('ExamScreen startup grace guards', () {
    final entry = DateTime(2026, 11, 26, 10, 0, 0);

    test('ignores non-critical violations during 5s startup grace period', () {
      expect(
        ExamScreen.isWithinStartupGrace(
          type: 'APP_SWITCH',
          entryTime: entry,
          now: entry.add(const Duration(milliseconds: 2500)),
        ),
        isTrue,
      );
      expect(
        ExamScreen.isWithinStartupGrace(
          type: 'FLOATING_WINDOW',
          entryTime: entry,
          now: entry.add(const Duration(milliseconds: 4999)),
        ),
        isTrue,
      );
      expect(
        ExamScreen.isWithinStartupGrace(
          type: 'APP_SWITCH',
          entryTime: entry,
          now: entry.add(const Duration(seconds: 5)),
        ),
        isFalse,
      );
    });

    test('never suppresses critical SCREEN_OFF or APP_RESTART violations', () {
      expect(
        ExamScreen.isWithinStartupGrace(
          type: 'SCREEN_OFF',
          entryTime: entry,
          now: entry.add(const Duration(seconds: 1)),
        ),
        isFalse,
      );
      expect(
        ExamScreen.isWithinStartupGrace(
          type: 'APP_RESTART_OR_DEVICE_OFF',
          entryTime: entry,
          now: entry.add(const Duration(seconds: 1)),
        ),
        isFalse,
      );
    });

    test('ignores brief OS startup transition across 5s boundary but catches real switches', () {
      // Pause started at 3.5s, resumed at 5.2s (1.7s transient dialog/WebView lag) -> ignored
      expect(
        ExamScreen.shouldIgnoreResumeViolation(
          entryTime: entry,
          awayStartTime: entry.add(const Duration(milliseconds: 3500)),
          now: entry.add(const Duration(milliseconds: 5200)),
        ),
        isTrue,
      );

      // Pause started at 1.0s, resumed at 5.5s (4.5s away) -> real switch, caught
      expect(
        ExamScreen.shouldIgnoreResumeViolation(
          entryTime: entry,
          awayStartTime: entry.add(const Duration(seconds: 1)),
          now: entry.add(const Duration(milliseconds: 5500)),
        ),
        isFalse,
      );

      // Normal post-startup switch (pause at 10s, resume at 11s) -> real switch, caught
      expect(
        ExamScreen.shouldIgnoreResumeViolation(
          entryTime: entry,
          awayStartTime: entry.add(const Duration(seconds: 10)),
          now: entry.add(const Duration(seconds: 11)),
        ),
        isFalse,
      );
    });
  });
}
