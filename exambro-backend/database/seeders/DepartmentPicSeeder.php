<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DepartmentPicSeeder extends Seeder
{
    public const DEPARTMENTS = ['AKUN', 'MPLB', 'DKV', 'TJKT', 'TM', 'TITL'];

    public function run(): void
    {
        foreach (self::DEPARTMENTS as $dept) {
            $slug = strtolower($dept);
            User::updateOrCreate(
                ['username' => "pic_{$slug}"],
                [
                    'name' => "PIC Jurusan {$dept}",
                    'email' => "pic.{$slug}@smkn1tirtamulya.online",
                    'password' => Hash::make("SatryaPIC{$dept}"),
                    'role' => 'admin',
                    'department' => $dept,
                    'status' => 'active',
                ]
            );
        }
    }
}
