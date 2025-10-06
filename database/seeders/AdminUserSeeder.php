<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se o usuário admin já existe
        $adminUser = User::where('email', 'contato@8bits.pro')->first();
        
        if (!$adminUser) {
            // Criar usuário admin se não existir
            User::create([
                'name' => '8bits Admin',
                'email' => 'contato@8bits.pro',
                'password' => Hash::make('AMESMASENHA2022*'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->command->info('✅ Usuário admin criado: contato@8bits.pro');
        } else {
            $this->command->info('ℹ️  Usuário admin já existe: contato@8bits.pro');
        }
    }
}