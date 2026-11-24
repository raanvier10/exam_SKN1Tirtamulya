<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function captcha(Request $request)
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $request->session()->put('admin_login_captcha', $code);

        $width = 160;
        $height = 48;
        $img = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($img, 245, 248, 255);
        imagefill($img, 0, 0, $bg);

        $navy = imagecolorallocate($img, 20, 44, 92);
        $blue = imagecolorallocate($img, 36, 87, 232);
        $lightBlue = imagecolorallocate($img, 180, 205, 248);

        // Garis & titik pengganggu agar tidak mudah dibaca bot/OCR
        for ($i = 0; $i < 6; $i++) {
            $lineColor = ($i % 2 === 0) ? $lightBlue : $blue;
            imageline(
                $img,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $lineColor
            );
        }

        for ($i = 0; $i < 90; $i++) {
            imagesetpixel($img, random_int(0, $width - 1), random_int(0, $height - 1), $lightBlue);
        }

        // Gambar tiap karakter dengan posisi acak & perbesaran GD built-in font
        $charWidth = 26;
        $startX = 16;
        for ($i = 0; $i < strlen($code); $i++) {
            $glyph = imagecreatetruecolor(14, 18);
            $gBg = imagecolorallocate($glyph, 245, 248, 255);
            imagefill($glyph, 0, 0, $gBg);
            $textColor = ($i % 2 === 0)
                ? imagecolorallocate($glyph, 20, 44, 92)
                : imagecolorallocate($glyph, 36, 87, 232);
            imagestring($glyph, 5, 2, 1, $code[$i], $textColor);

            $rotated = imagerotate($glyph, random_int(-14, 14), $gBg);
            $rw = imagesx($rotated);
            $rh = imagesy($rotated);
            $destX = $startX + ($i * $charWidth) + random_int(-2, 2);
            $destY = random_int(4, 12);
            imagecopyresampled($img, $rotated, $destX, $destY, 0, 0, (int) ($rw * 1.45), (int) ($rh * 1.45), $rw, $rh);
            imagedestroy($glyph);
            imagedestroy($rotated);
        }

        // Efek sedikit buram agar menyulitkan OCR bot
        imagefilter($img, IMG_FILTER_GAUSSIAN_BLUR);

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return response($pngData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._@-]+$/'],
            'password' => ['required', 'string', 'max:255'],
            'captcha' => ['required', 'string', 'size:5'],
        ], [
            'username.regex' => 'Format username tidak valid.',
            'captcha.required' => 'Kode verifikasi wajib diisi.',
            'captcha.size' => 'Kode verifikasi harus 5 karakter.',
        ]);

        // Verifikasi CAPTCHA sekali pakai
        $expectedCaptcha = (string) $request->session()->pull('admin_login_captcha', '');
        if ($expectedCaptcha === '' || strtoupper(trim($credentials['captcha'])) !== $expectedCaptcha) {
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['captcha' => 'Kode verifikasi tidak sesuai. Silakan coba lagi.']);
        }

        // Proteksi brute-force per username + IP
        $throttleKey = Str::lower(trim($credentials['username'])) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik."]);
        }

        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
            'status' => 'active',
        ])) {
            $user = Auth::user();
            if (in_array($user->role, ['admin', 'guru'], true)) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }

            Auth::logout();
            return back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'Akses ditolak. Portal ini khusus Administrator dan Guru.']);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withInput($request->only('username'))
            ->withErrors(['username' => 'Username atau password salah.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function showChangePasswordForm()
    {
        return view('admin.auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed', 'regex:/^\S*$/u'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus :min karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        $request->session()->regenerate();

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
