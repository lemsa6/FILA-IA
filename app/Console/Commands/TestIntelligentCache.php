<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Services\IntelligentAIService;
use Illuminate\Console\Command;

class TestIntelligentCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test-cache {api_key_id? : ID da chave API para testar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o sistema de cache inteligente';

    /**
     * Execute the console command.
     */
    public function handle(IntelligentAIService $intelligentAIService)
    {
        $apiKeyId = $this->argument('api_key_id');

        if (!$apiKeyId) {
            // Busca uma chave API válida
            $apiKey = ApiKey::first();
            if (!$apiKey) {
                $this->error('Nenhuma chave API encontrada no sistema!');
                return 1;
            }
            $apiKeyId = $apiKey->id;
            $this->info("Usando chave API: {$apiKeyId}");
        }

        $this->info('🧪 Testando sistema de cache inteligente...');
        $this->newLine();

        // 1. Testa definição de contexto base
        $this->info('1️⃣ Definindo contexto base...');
        $baseContext = "Este é um sistema de atendimento médico especializado em cardiologia. 
        Oferecemos consultas, exames cardíacos, ecocardiogramas e tratamentos para doenças cardiovasculares. 
        Nossa equipe é composta por cardiologistas experientes e utilizamos tecnologia de ponta para diagnósticos precisos.";
        
        $success = $intelligentAIService->setBaseContext($apiKeyId, $baseContext, [
            'domain' => 'medicina',
            'specialty' => 'cardiologia',
            'version' => '1.0'
        ]);

        if ($success) {
            $this->info('✅ Contexto base definido com sucesso!');
        } else {
            $this->error('❌ Erro ao definir contexto base!');
            return 1;
        }

        // 2. Testa obtenção de contexto base
        $this->info('2️⃣ Obtendo contexto base...');
        $contextInfo = $intelligentAIService->getBaseContextInfo($apiKeyId);
        
        if ($contextInfo) {
            $this->info("✅ Contexto base obtido: {$contextInfo['content_length']} caracteres, ~{$contextInfo['estimated_tokens']} tokens");
        } else {
            $this->error('❌ Erro ao obter contexto base!');
            return 1;
        }

        // 3. Testa geração de resposta com contexto
        $this->info('3️⃣ Testando geração de resposta com contexto...');
        
        try {
            $result = $intelligentAIService->generateIntelligentResponse(
                'Quais exames cardíacos vocês oferecem?',
                $apiKeyId,
                'test-session-1'
            );

            $this->info('✅ Resposta gerada com sucesso!');
            $this->info("📝 Resposta: " . substr($result['response'] ?? 'N/A', 0, 100) . '...');
            
            if (isset($result['_cache_info'])) {
                $this->info("🔍 Info cache: Contexto base usado: " . ($result['_cache_info']['base_context_used'] ? 'Sim' : 'Não'));
                $this->info("🔍 Info cache: Comprimento conversa: {$result['_cache_info']['conversation_length']}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro ao gerar resposta: {$e->getMessage()}");
            return 1;
        }

        // 4. Testa segunda pergunta (deve usar contexto)
        $this->info('4️⃣ Testando segunda pergunta (deve usar contexto)...');
        
        try {
            $result2 = $intelligentAIService->generateIntelligentResponse(
                'E qual é o valor da consulta?',
                $apiKeyId,
                'test-session-1'
            );

            $this->info('✅ Segunda resposta gerada com sucesso!');
            $this->info("📝 Resposta: " . substr($result2['response'] ?? 'N/A', 0, 100) . '...');
            
            if (isset($result2['_cache_info'])) {
                $this->info("🔍 Info cache: Contexto base usado: " . ($result2['_cache_info']['base_context_used'] ? 'Sim' : 'Não'));
                $this->info("🔍 Info cache: Comprimento conversa: {$result2['_cache_info']['conversation_length']}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro ao gerar segunda resposta: {$e->getMessage()}");
            return 1;
        }

        // 5. Testa estatísticas
        $this->info('5️⃣ Obtendo estatísticas de cache...');
        $stats = $intelligentAIService->getCacheStats($apiKeyId);
        
        $this->info('✅ Estatísticas obtidas:');
        $this->info("   - Tem contexto base: " . ($stats['base_context']['has_context'] ? 'Sim' : 'Não'));
        if ($stats['base_context']['has_context']) {
            $this->info("   - Tamanho contexto: {$stats['base_context']['content_length']} caracteres");
            $this->info("   - Tokens estimados: {$stats['base_context']['estimated_tokens']}");
        }
        $this->info("   - Sessões ativas: {$stats['sessions']['active_count']}");
        $this->info("   - Total interações: {$stats['sessions']['total_interactions']}");

        $this->newLine();
        $this->info('🎉 Teste concluído com sucesso!');
        $this->info('O sistema de cache inteligente está funcionando perfeitamente! 🚀');

        return 0;
    }
}
