<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user 
                           {--email=contato@8bits.pro : Email do usuário admin}
                           {--name=8bits Admin : Nome do usuário admin}
                           {--password=AMESMASENHA2022* : Senha do usuário admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria ou atualiza o usuário administrador do sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password');

        // Verificar se o usuário já existe
        $user = User::where('email', $email)->first();

        if ($user) {
            // Atualizar usuário existente
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            
            $this->info("✅ Usuário admin atualizado: {$email}");
        } else {
            // Criar novo usuário
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            
            $this->info("🎉 Usuário admin criado: {$email}");
        }

        $this->line("📧 Email: {$email}");
        $this->line("👤 Nome: {$name}");
        $this->line("🔐 Senha: {$password}");
        
        return Command::SUCCESS;
    }
}