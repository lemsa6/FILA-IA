<?php

namespace App\Services\Resilience;

use Closure;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreaker
{
    /**
     * Estado do circuito
     */
    const STATE_CLOSED = 'closed';    // Funcionando normalmente
    const STATE_OPEN = 'open';        // Falhas detectadas, circuito aberto
    const STATE_HALF_OPEN = 'half_open'; // Tentando recuperar

    /**
     * Nome do serviço
     *
     * @var string
     */
    protected $service;

    /**
     * Número de falhas necessárias para abrir o circuito
     *
     * @var int
     */
    protected $failureThreshold;

    /**
     * Tempo em segundos para tentar fechar o circuito novamente
     *
     * @var int
     */
    protected $resetTimeout;

    /**
     * Construtor
     *
     * @param string $service Nome do serviço
     * @param int $failureThreshold Número de falhas para abrir o circuito
     * @param int $resetTimeout Tempo em segundos para resetar
     */
    public function __construct(string $service, int $failureThreshold = 5, int $resetTimeout = 60)
    {
        $this->service = $service;
        $this->failureThreshold = $failureThreshold;
        $this->resetTimeout = $resetTimeout;
    }

    /**
     * Executa uma função com proteção de circuit breaker
     *
     * @param Closure $func Função a ser executada
     * @param Closure|null $fallback Função de fallback em caso de circuito aberto
     * @return mixed
     * @throws Exception
     */
    public function execute(Closure $func, ?Closure $fallback = null)
    {
        $state = $this->getState();

        // Se o circuito estiver aberto
        if ($state === self::STATE_OPEN) {
            $lastFailure = $this->getLastFailureTime();
            
            // Verifica se já passou o tempo para tentar novamente
            if ((time() - $lastFailure) > $this->resetTimeout) {
                $this->setState(self::STATE_HALF_OPEN);
                Log::info("Circuit breaker para {$this->service} mudou para half-open (tentando recuperação)");
            } else {
                // Circuito ainda aberto
                Log::warning("Circuit breaker para {$this->service} está aberto. Usando fallback ou lançando exceção.");
                
                if ($fallback) {
                    return $fallback();
                }
                
                throw new Exception("Serviço {$this->service} indisponível (circuit breaker aberto)");
            }
        }

        try {
            // Executa a função
            $result = $func();
            
            // Se estava em half-open e funcionou, fecha o circuito
            if ($state === self::STATE_HALF_OPEN) {
                $this->setState(self::STATE_CLOSED);
                $this->resetFailureCount();
                Log::info("Circuit breaker para {$this->service} fechado novamente após recuperação automática");
            }
            
            return $result;
        } catch (Exception $e) {
            // Incrementa contador de falhas
            $this->incrementFailureCount();
            $failureCount = $this->getFailureCount();
            
            Log::warning("Falha no serviço {$this->service}. Contador: {$failureCount}/{$this->failureThreshold}", [
                'exception' => $e->getMessage(),
            ]);
            
            // Se atingiu o limite de falhas, abre o circuito
            if ($failureCount >= $this->failureThreshold) {
                $this->setState(self::STATE_OPEN);
                $this->setLastFailureTime(time());
                Log::error("Circuit breaker para {$this->service} aberto após {$failureCount} falhas");
            }
            
            // Se tem fallback, usa
            if ($fallback) {
                return $fallback();
            }
            
            // Caso contrário, propaga a exceção
            throw $e;
        }
    }

    /**
     * Obtém o estado atual do circuit breaker
     *
     * @return string
     */
    protected function getState(): string
    {
        return Cache::get($this->getStateKey(), self::STATE_CLOSED);
    }

    /**
     * Define o estado do circuit breaker
     *
     * @param string $state
     * @return void
     */
    protected function setState(string $state): void
    {
        Cache::put($this->getStateKey(), $state, 3600);
    }

    /**
     * Incrementa o contador de falhas
     *
     * @return void
     */
    protected function incrementFailureCount(): void
    {
        $count = Cache::get($this->getFailureCountKey(), 0);
        Cache::put($this->getFailureCountKey(), $count + 1, 3600);
    }

    /**
     * Obtém o contador de falhas atual
     *
     * @return int
     */
    protected function getFailureCount(): int
    {
        return Cache::get($this->getFailureCountKey(), 0);
    }

    /**
     * Reseta o contador de falhas
     *
     * @return void
     */
    protected function resetFailureCount(): void
    {
        Cache::put($this->getFailureCountKey(), 0, 3600);
    }

    /**
     * Define o timestamp da última falha
     *
     * @param int $timestamp
     * @return void
     */
    protected function setLastFailureTime(int $timestamp): void
    {
        Cache::put($this->getLastFailureTimeKey(), $timestamp, 3600);
    }

    /**
     * Obtém o timestamp da última falha
     *
     * @return int
     */
    protected function getLastFailureTime(): int
    {
        return Cache::get($this->getLastFailureTimeKey(), 0);
    }

    /**
     * Gera a chave para o estado no cache
     *
     * @return string
     */
    protected function getStateKey(): string
    {
        return "circuit_breaker:{$this->service}:state";
    }

    /**
     * Gera a chave para o contador de falhas no cache
     *
     * @return string
     */
    protected function getFailureCountKey(): string
    {
        return "circuit_breaker:{$this->service}:failure_count";
    }

    /**
     * Gera a chave para o timestamp da última falha no cache
     *
     * @return string
     */
    protected function getLastFailureTimeKey(): string
    {
        return "circuit_breaker:{$this->service}:last_failure_time";
    }

    /**
     * Reseta manualmente o circuit breaker para o estado fechado
     * Útil quando o serviço volta a funcionar
     *
     * @return void
     */
    public function reset(): void
    {
        $this->setState(self::STATE_CLOSED);
        $this->resetFailureCount();
        $this->setLastFailureTime(0);
        
        Log::info("Circuit breaker para {$this->service} resetado manualmente");
    }
} 