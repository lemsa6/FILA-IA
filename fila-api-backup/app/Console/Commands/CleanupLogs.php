<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:cleanup {--days=30 : Número de dias para manter logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpa logs antigos do banco de dados e comprime logs de arquivo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Limpando logs anteriores a {$cutoffDate->format('Y-m-d')}");

        // Limpa logs do banco de dados
        try {
            $count = DB::table('logs')
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            $this->info("Removidos {$count} registros de log do banco de dados");
        } catch (\Exception $e) {
            $this->error("Erro ao limpar logs do banco de dados: {$e->getMessage()}");
            Log::error('Erro ao limpar logs do banco de dados', [
                'exception' => $e->getMessage(),
            ]);
        }

        // Comprime logs de arquivo antigos
        $this->compressOldLogFiles($days);

        $this->info('Limpeza de logs concluída');
    }

    /**
     * Comprime arquivos de log antigos
     *
     * @param int $days
     * @return void
     */
    protected function compressOldLogFiles(int $days): void
    {
        $logPath = storage_path('logs');
        $files = glob("{$logPath}/*.log");
        $cutoffTime = time() - ($days * 86400);
        $compressedCount = 0;

        foreach ($files as $file) {
            // Ignora arquivos já comprimidos
            if (str_ends_with($file, '.gz')) {
                continue;
            }

            // Verifica se o arquivo é antigo o suficiente
            if (filemtime($file) < $cutoffTime) {
                $compressedFile = "{$file}.gz";
                
                try {
                    // Comprime o arquivo
                    $fileContent = file_get_contents($file);
                    $compressed = gzencode($fileContent, 9);
                    file_put_contents($compressedFile, $compressed);
                    
                    // Verifica se a compressão foi bem-sucedida
                    if (file_exists($compressedFile) && filesize($compressedFile) > 0) {
                        unlink($file);
                        $compressedCount++;
                    }
                } catch (\Exception $e) {
                    $this->error("Erro ao comprimir arquivo {$file}: {$e->getMessage()}");
                    Log::error('Erro ao comprimir arquivo de log', [
                        'file' => $file,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Comprimidos {$compressedCount} arquivos de log");
    }
} 