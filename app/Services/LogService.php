<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class LogService
{
    /**
     * Níveis de log
     */
    const LEVEL_INFO = 'info';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_EMERGENCY = 'emergency';

    /**
     * Categorias de log
     */
    const CATEGORY_AUTH = 'auth';
    const CATEGORY_OLLAMA = 'ollama';
    const CATEGORY_QUEUE = 'queue';
    const CATEGORY_DATABASE = 'database';
    const CATEGORY_VALIDATION = 'validation';
    const CATEGORY_TIMEOUT = 'timeout';
    const CATEGORY_SYSTEM = 'system';

    /**
     * Logger para logs persistentes
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Construtor
     */
    public function __construct()
    {
        // Configura o logger com rotação diária
        $this->logger = new Logger('persistent');
        $this->logger->pushHandler(
            new RotatingFileHandler(
                storage_path('logs/persistent.log'),
                config('logging.persistent.days', 30),
                Logger::INFO
            )
        );
    }

    /**
     * Registra um log persistente
     *
     * @param string $level
     * @param string $context
     * @param string $message
     * @param array $metadata
     * @return void
     */
    public function log(string $level, string $context, string $message, array $metadata = []): void
    {
        // Prepara os dados do log
        $logData = [
            'timestamp' => now()->toIso8601String(),
            'level' => $level,
            'context' => $context,
            'message' => $message,
            'metadata' => $metadata,
        ];

        // Adiciona informações de request se disponíveis
        if (request()->id) {
            $logData['request_id'] = request()->id;
        }

        if (request()->apiKey) {
            $logData['api_key_id'] = request()->apiKey->id;
        }

        // Adiciona informações do host
        $logData['host'] = gethostname();
        
        // Mascara dados sensíveis
        $logData = $this->maskSensitiveData($logData);

        // Registra no arquivo de log
        $this->logger->log(
            $this->mapLevel($level),
            $message,
            $logData
        );

        // Registra no log do Laravel para compatibilidade
        Log::log($this->mapLevel($level), $message, $logData);

        // Persiste no banco de dados se configurado
        if (config('logging.persistent.database', false)) {
            try {
                DB::table('logs')->insert([
                    'level' => $level,
                    'context' => $context,
                    'message' => $message,
                    'metadata' => json_encode($metadata),
                    'request_id' => $logData['request_id'] ?? null,
                    'api_key_id' => $logData['api_key_id'] ?? null,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao persistir log no banco de dados', [
                    'exception' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Registra um log de nível INFO
     *
     * @param string $context
     * @param string $message
     * @param array $metadata
     * @return void
     */
    public function info(string $context, string $message, array $metadata = []): void
    {
        $this->log(self::LEVEL_INFO, $context, $message, $metadata);
    }

    /**
     * Registra um log de nível WARNING
     *
     * @param string $context
     * @param string $message
     * @param array $metadata
     * @return void
     */
    public function warning(string $context, string $message, array $metadata = []): void
    {
        $this->log(self::LEVEL_WARNING, $context, $message, $metadata);
    }

    /**
     * Registra um log de nível ERROR
     *
     * @param string $context
     * @param string $message
     * @param array $metadata
     * @return void
     */
    public function error(string $context, string $message, array $metadata = []): void
    {
        $this->log(self::LEVEL_ERROR, $context, $message, $metadata);
    }

    /**
     * Registra um log de nível CRITICAL
     *
     * @param string $context
     * @param string $message
     * @param array $metadata
     * @return void
     */
    public function critical(string $context, string $message, array $metadata = []): void
    {
        $this->log(self::LEVEL_CRITICAL, $context, $message, $metadata);
    }

    /**
     * Registra um log de nível EMERGENCY
     *
     * @param string $context
     * @param string $message
     * @param array $metadata
     * @return void
     */
    public function emergency(string $context, string $message, array $metadata = []): void
    {
        $this->log(self::LEVEL_EMERGENCY, $context, $message, $metadata);
    }

    /**
     * Mapeia o nível de log para o equivalente do Monolog
     *
     * @param string $level
     * @return string
     */
    protected function mapLevel(string $level): string
    {
        $map = [
            self::LEVEL_INFO => 'info',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_ERROR => 'error',
            self::LEVEL_CRITICAL => 'critical',
            self::LEVEL_EMERGENCY => 'emergency',
        ];

        return $map[$level] ?? 'info';
    }

    /**
     * Mascara dados sensíveis nos logs
     *
     * @param array $data
     * @return array
     */
    protected function maskSensitiveData(array $data): array
    {
        // Lista de campos sensíveis a serem mascarados
        $sensitiveFields = [
            'password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'cpf',
            'cnpj',
            'rg',
            'email',
        ];

        // Função recursiva para mascarar dados
        $mask = function ($item) use (&$mask, $sensitiveFields) {
            if (!is_array($item)) {
                return $item;
            }

            foreach ($item as $key => $value) {
                if (is_array($value)) {
                    $item[$key] = $mask($value);
                } elseif (is_string($value) && in_array(strtolower($key), $sensitiveFields)) {
                    $item[$key] = '********';
                }
            }

            return $item;
        };

        return $mask($data);
    }
} 