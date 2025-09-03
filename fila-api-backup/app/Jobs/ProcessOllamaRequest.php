<?php

namespace App\Jobs;

use App\Models\Request as OllamaRequest;
use App\Services\OllamaService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessOllamaRequest implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número máximo de tentativas
     *
     * @var int
     */
    public $tries = 3;

    /**
     * Tempo de espera entre tentativas em segundos
     *
     * @var array
     */
    public $backoff = [10, 60, 300]; // 10s, 1min, 5min

    /**
     * Nome da fila onde o job será processado
     *
     * @var string
     */
    public $queue = 'ollama-requests';

    /**
     * A requisição a ser processada
     *
     * @var \App\Models\Request
     */
    protected $request;

    /**
     * Tempo mínimo entre o início de requisições em segundos
     * 
     * @var int
     */
    protected $minTimeBetweenRequests = 2;

    /**
     * Chave de cache para controlar o tempo da última requisição
     * 
     * @var string
     */
    protected $lastRequestTimeKey = 'ollama_last_request_time';

    /**
     * Create a new job instance.
     */
    public function __construct(OllamaRequest $request)
    {
        $this->request = $request;
    }

    /**
     * O identificador único do job.
     *
     * @return string
     */
    public function uniqueId()
    {
        // Garantir que apenas um job seja processado por vez
        return 'ollama_processing_lock';
    }

    /**
     * Execute the job.
     */
    public function handle(OllamaService $ollamaService): void
    {
        try {
            // Verifica quando foi a última requisição
            $lastRequestTime = Cache::get($this->lastRequestTimeKey);
            $now = microtime(true);
            
            if ($lastRequestTime) {
                $timeSinceLastRequest = $now - $lastRequestTime;
                
                // Se não passou tempo suficiente desde o início da última requisição, aguarda
                if ($timeSinceLastRequest < $this->minTimeBetweenRequests) {
                    $sleepTime = ceil($this->minTimeBetweenRequests - $timeSinceLastRequest);
                    Log::info("Aguardando {$sleepTime}s para respeitar o intervalo mínimo entre requisições", [
                        'request_id' => $this->request->id,
                    ]);
                    sleep($sleepTime);
                }
            }
            
            // Marca o tempo de início desta requisição
            Cache::put($this->lastRequestTimeKey, microtime(true), 3600);

            // Atualiza o status da requisição
            $this->request->status = 'processing';
            $this->request->attempts += 1;
            $this->request->save();

            // Decodifica o conteúdo da requisição
            $content = json_decode($this->request->content, true);
            $prompt = $content['prompt'] ?? '';
            $parameters = $content['parameters'] ?? [];

            // Registra o tempo de início
            $startTime = microtime(true);

            // Processa a requisição
            $result = $ollamaService->generateCompletion($prompt, $parameters);

            // Calcula o tempo de processamento
            $processingTime = (int) ((microtime(true) - $startTime) * 1000); // em milissegundos

            // Atualiza a requisição com o resultado
            $this->request->status = 'completed';
            $this->request->result = json_encode($result);
            $this->request->processing_time = $processingTime;
            $this->request->completed_at = now();
            $this->request->save();

            Log::info('Requisição processada com sucesso', [
                'request_id' => $this->request->id,
                'processing_time' => $processingTime,
            ]);
        } catch (Exception $e) {
            // Registra o erro
            Log::error('Erro ao processar requisição', [
                'request_id' => $this->request->id,
                'error' => $e->getMessage(),
                'attempt' => $this->request->attempts,
            ]);

            // Se atingiu o número máximo de tentativas, marca como falha
            if ($this->attempts() >= $this->tries) {
                $this->request->status = 'failed';
                $this->request->error = $e->getMessage();
                $this->request->save();
            } else {
                // Caso contrário, lança a exceção para que o job seja tentado novamente
                throw $e;
            }
        }
    }
}
