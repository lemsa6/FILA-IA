<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cria o usuário administrador (atualizado para v1.3.0)
        User::firstOrCreate(
            ['email' => 'contato@8bits.pro'],
            [
                'name' => '8bits Admin',
                'password' => Hash::make('AMESMASENHA2022*'),
                'email_verified_at' => now(),
            ]
        );
    }
}
