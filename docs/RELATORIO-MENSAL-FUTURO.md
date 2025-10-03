# 📊 Relatório Mensal Automatizado - Implementação Futura

## 🎯 Visão Geral

Sistema automatizado para geração de relatórios mensais em PDF com métricas de uso e custos do FILA-IA, incluindo gráficos, análises comparativas e envio automático por email.

## 📋 Funcionalidades Planejadas

### 📈 Conteúdo do Relatório PDF

#### **Seção 1: Resumo Executivo**
- Total gasto mensal (BRL/USD)
- Variação percentual vs mês anterior
- Principais métricas consolidadas
- Status geral do sistema

#### **Seção 2: Análise por API Key**
- Breakdown de custos por API
- Ranking de uso (mais utilizadas)
- Percentual de participação
- Análise de eficiência (custo/requisição)

#### **Seção 3: Gráficos e Visualizações**
- **Gráfico Pizza**: Distribuição de custos por API
- **Gráfico Barras**: Evolução diária do mês
- **Gráfico Linha**: Tendência de tokens/custos
- **Heatmap**: Padrões de uso por dia/hora

#### **Seção 4: Métricas Detalhadas**
- Total de requisições processadas
- Tokens consumidos (entrada/saída)
- Tempo médio de processamento
- Taxa de sucesso/falha
- Modelos de IA mais utilizados

#### **Seção 5: Análise Comparativa**
- Mês atual vs mês anterior
- Tendências de crescimento
- Projeções para próximo mês
- Recomendações de otimização

## 🏗️ Arquitetura Técnica

### 📦 Dependências Necessárias

```json
{
  "barryvdh/laravel-dompdf": "^2.0",
  "consoletvs/charts": "^6.0",
  "google/apiclient": "^2.15",
  "maatwebsite/excel": "^3.1"
}
```

### 🗂️ Estrutura de Arquivos

```
app/
├── Console/Commands/
│   └── GenerateMonthlyReportCommand.php
├── Services/
│   ├── MonthlyReportService.php
│   ├── ReportDataService.php
│   └── GoogleIntegrationService.php
├── Jobs/
│   ├── GenerateMonthlyReportJob.php
│   └── SendMonthlyReportJob.php
├── Mail/
│   └── MonthlyReportMail.php
└── Http/Controllers/Admin/
    └── ReportsController.php

resources/
├── views/reports/
│   ├── monthly-pdf.blade.php
│   ├── email-template.blade.php
│   └── partials/
│       ├── charts.blade.php
│       ├── summary.blade.php
│       └── api-breakdown.blade.php
└── assets/
    └── report-styles.css

storage/
└── app/reports/
    ├── monthly/
    │   ├── 2025-09/
    │   └── 2025-10/
    └── templates/
```

## 🔧 Implementação Detalhada

### 1. Command Principal

```php
<?php
// app/Console/Commands/GenerateMonthlyReportCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonthlyReportService;

class GenerateMonthlyReportCommand extends Command
{
    protected $signature = 'report:monthly {--month=} {--year=} {--send-email}';
    protected $description = 'Gera relatório mensal de uso e custos';

    public function handle(MonthlyReportService $reportService)
    {
        $month = $this->option('month') ?? now()->subMonth()->month;
        $year = $this->option('year') ?? now()->subMonth()->year;
        
        $this->info("Gerando relatório para {$month}/{$year}...");
        
        $reportPath = $reportService->generateReport($month, $year);
        
        if ($this->option('send-email')) {
            $reportService->sendByEmail($reportPath);
            $this->info('Relatório enviado por email!');
        }
        
        $this->info("Relatório gerado: {$reportPath}");
    }
}
```

### 2. Service Principal

```php
<?php
// app/Services/MonthlyReportService.php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Request as GPTRequest;
use Carbon\Carbon;

class MonthlyReportService
{
    public function generateReport(int $month, int $year): string
    {
        $data = $this->collectReportData($month, $year);
        $charts = $this->generateCharts($data);
        
        $pdf = Pdf::loadView('reports.monthly-pdf', [
            'data' => $data,
            'charts' => $charts,
            'period' => Carbon::create($year, $month)->format('F Y'),
            'generated_at' => now()
        ]);
        
        $filename = "relatorio-mensal-{$year}-{$month}.pdf";
        $path = storage_path("app/reports/monthly/{$year}-{$month}/{$filename}");
        
        // Criar diretório se não existir
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        $pdf->save($path);
        
        return $path;
    }
    
    private function collectReportData(int $month, int $year): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        
        // Dados do mês atual
        $currentData = $this->getMonthData($startDate, $endDate);
        
        // Dados do mês anterior para comparação
        $previousStart = $startDate->copy()->subMonth();
        $previousEnd = $endDate->copy()->subMonth();
        $previousData = $this->getMonthData($previousStart, $previousEnd);
        
        return [
            'current' => $currentData,
            'previous' => $previousData,
            'comparison' => $this->calculateComparison($currentData, $previousData),
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ];
    }
    
    private function getMonthData(Carbon $start, Carbon $end): array
    {
        $query = GPTRequest::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);
            
        return [
            'total_requests' => $query->count(),
            'total_cost_usd' => $query->sum('cost_usd'),
            'total_cost_brl' => $query->sum('cost_brl'),
            'total_tokens_input' => $query->sum('tokens_input'),
            'total_tokens_output' => $query->sum('tokens_output'),
            'avg_processing_time' => $query->avg('processing_time'),
            'by_api_key' => $this->getDataByApiKey($start, $end),
            'daily_stats' => $this->getDailyStats($start, $end),
            'model_usage' => $this->getModelUsage($start, $end)
        ];
    }
    
    private function getDataByApiKey(Carbon $start, Carbon $end): array
    {
        return GPTRequest::with('apiKey')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                api_key_id,
                COUNT(*) as requests,
                SUM(cost_usd) as cost_usd,
                SUM(cost_brl) as cost_brl,
                SUM(tokens_input + tokens_output) as total_tokens
            ')
            ->groupBy('api_key_id')
            ->orderBy('cost_brl', 'desc')
            ->get()
            ->toArray();
    }
    
    private function generateCharts(array $data): array
    {
        return [
            'cost_distribution' => $this->createCostDistributionChart($data['current']['by_api_key']),
            'daily_evolution' => $this->createDailyEvolutionChart($data['current']['daily_stats']),
            'model_usage' => $this->createModelUsageChart($data['current']['model_usage'])
        ];
    }
}
```

### 3. Template PDF

```blade
{{-- resources/views/reports/monthly-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Mensal FILA-IA - {{ $period }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; }
        .header { background: #4f46e5; color: white; padding: 20px; text-align: center; }
        .summary { background: #f8fafc; padding: 20px; margin: 20px 0; }
        .metric { display: inline-block; width: 23%; text-align: center; margin: 1%; }
        .chart { page-break-inside: avoid; margin: 20px 0; }
        .api-table { width: 100%; border-collapse: collapse; }
        .api-table th, .api-table td { border: 1px solid #ddd; padding: 8px; }
        .footer { text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>📊 RELATÓRIO MENSAL</h1>
        <h2>FILA-IA - {{ $period }}</h2>
        <p>Gerado em {{ $generated_at->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Resumo Executivo -->
    <div class="summary">
        <h2>💰 Resumo Executivo</h2>
        <div class="metric">
            <h3>R$ {{ number_format($data['current']['total_cost_brl'], 2, ',', '.') }}</h3>
            <p>Total Gasto (BRL)</p>
        </div>
        <div class="metric">
            <h3>${{ number_format($data['current']['total_cost_usd'], 6) }}</h3>
            <p>Total Gasto (USD)</p>
        </div>
        <div class="metric">
            <h3>{{ number_format($data['current']['total_requests']) }}</h3>
            <p>Requisições</p>
        </div>
        <div class="metric">
            <h3>{{ $data['comparison']['cost_variation'] > 0 ? '+' : '' }}{{ number_format($data['comparison']['cost_variation'], 1) }}%</h3>
            <p>vs Mês Anterior</p>
        </div>
    </div>

    <!-- Breakdown por API -->
    <h2>🔑 Análise por API Key</h2>
    <table class="api-table">
        <thead>
            <tr>
                <th>API Key</th>
                <th>Requisições</th>
                <th>Custo BRL</th>
                <th>Custo USD</th>
                <th>% do Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['current']['by_api_key'] as $api)
            <tr>
                <td>{{ $api['apiKey']['name'] ?? 'N/A' }}</td>
                <td>{{ number_format($api['requests']) }}</td>
                <td>R$ {{ number_format($api['cost_brl'], 4, ',', '.') }}</td>
                <td>${{ number_format($api['cost_usd'], 6) }}</td>
                <td>{{ number_format(($api['cost_brl'] / $data['current']['total_cost_brl']) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Gráficos -->
    <div class="chart">
        <h2>📈 Gráficos e Análises</h2>
        {!! $charts['cost_distribution'] !!}
        {!! $charts['daily_evolution'] !!}
        {!! $charts['model_usage'] !!}
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <p>FILA-IA v{{ config('app.version', '1.1.0') }} - Relatório gerado automaticamente</p>
    </div>
</body>
</html>
```

### 4. Agendamento Automático

```php
<?php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Gera relatório mensal todo dia 1º às 08:00
    $schedule->command('report:monthly --send-email')
        ->monthlyOn(1, '08:00')
        ->timezone('America/Sao_Paulo')
        ->emailOutputOnFailure(config('mail.admin_email'));
        
    // Backup dos relatórios (opcional)
    $schedule->command('backup:reports')
        ->weekly()
        ->sundays()
        ->at('02:00');
}
```

### 5. Integração com Email

```php
<?php
// app/Mail/MonthlyReportMail.php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class MonthlyReportMail extends Mailable
{
    public function __construct(
        private string $reportPath,
        private string $period,
        private array $summary
    ) {}

    public function build()
    {
        return $this->subject("📊 Relatório Mensal FILA-IA - {$this->period}")
            ->view('emails.monthly-report')
            ->attach($this->reportPath, [
                'as' => "relatorio-mensal-{$this->period}.pdf",
                'mime' => 'application/pdf'
            ])
            ->with([
                'period' => $this->period,
                'summary' => $this->summary
            ]);
    }
}
```

## 🔐 Configurações Necessárias

### Environment Variables

```env
# Relatórios
REPORTS_ENABLED=true
REPORTS_EMAIL_RECIPIENTS="admin@empresa.com,financeiro@empresa.com"
REPORTS_STORAGE_DAYS=365

# Google Integration (Opcional)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=your_redirect_uri
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
```

### Configuração de Email

```php
// config/reports.php
return [
    'monthly' => [
        'enabled' => env('REPORTS_ENABLED', true),
        'recipients' => explode(',', env('REPORTS_EMAIL_RECIPIENTS', '')),
        'storage_days' => env('REPORTS_STORAGE_DAYS', 365),
        'template' => 'reports.monthly-pdf',
    ],
    
    'google' => [
        'enabled' => env('GOOGLE_INTEGRATION_ENABLED', false),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'drive_folder' => env('GOOGLE_DRIVE_FOLDER_ID'),
    ]
];
```

## 📅 Cronograma de Implementação

### Fase 1: Base (4-6 horas)
- [ ] Estrutura de arquivos
- [ ] Command básico
- [ ] Service de coleta de dados
- [ ] Template PDF simples

### Fase 2: Gráficos (3-4 horas)
- [ ] Integração com Charts
- [ ] Gráficos de pizza, barras e linha
- [ ] Estilização avançada do PDF

### Fase 3: Automação (2-3 horas)
- [ ] Scheduler configuration
- [ ] Job assíncrono
- [ ] Sistema de email

### Fase 4: Integrações (3-4 horas)
- [ ] Google Drive (opcional)
- [ ] Gmail API (opcional)
- [ ] Interface administrativa

### Fase 5: Testes e Refinamento (2-3 horas)
- [ ] Testes unitários
- [ ] Validação de dados
- [ ] Otimização de performance

## 🎯 Benefícios Esperados

- **📊 Visibilidade**: Controle total dos custos mensais
- **⚡ Automação**: Zero intervenção manual
- **📈 Análises**: Tendências e comparativos
- **💰 Economia**: Identificação de otimizações
- **📧 Conveniência**: Entrega automática por email
- **📱 Acessibilidade**: Relatórios em PDF profissional

## 🔮 Funcionalidades Futuras

- **Dashboard interativo** para visualização online
- **Alertas inteligentes** para gastos anômalos
- **Previsões de custo** baseadas em ML
- **Integração com sistemas de billing**
- **Relatórios personalizáveis** por departamento
- **API para integração externa**

---

**📝 Nota**: Esta documentação serve como guia completo para implementação futura do sistema de relatórios mensais automatizados do FILA-IA.

**🚀 Status**: Aguardando aprovação e priorização para desenvolvimento.
