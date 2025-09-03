# Script PowerShell para otimizar assets
Write-Host "🚀 Otimizando assets para performance máxima..." -ForegroundColor Green

# Função para comprimir arquivos
function Compress-File {
    param([string]$FilePath)
    
    if (Test-Path $FilePath) {
        Write-Host "📦 Comprimindo: $FilePath" -ForegroundColor Yellow
        
        try {
            # Ler o arquivo
            $content = Get-Content $FilePath -Raw -Encoding UTF8
            
            # Comprimir com Gzip
            $gzipPath = $FilePath + ".gz"
            $contentBytes = [System.Text.Encoding]::UTF8.GetBytes($content)
            
            # Usar .NET para compressão Gzip
            $ms = New-Object System.IO.MemoryStream
            $gzip = New-Object System.IO.Compression.GzipStream($ms, [System.IO.Compression.CompressionMode]::Compress)
            $gzip.Write($contentBytes, 0, $contentBytes.Length)
            $gzip.Close()
            
            # Salvar arquivo comprimido
            [System.IO.File]::WriteAllBytes($gzipPath, $ms.ToArray())
            $ms.Close()
            
            # Calcular redução
            $originalSize = (Get-Item $FilePath).Length
            $compressedSize = (Get-Item $gzipPath).Length
            $reduction = [math]::Round((($originalSize - $compressedSize) / $originalSize) * 100, 2)
            
            Write-Host "✅ Comprimido: $gzipPath (Redução: $reduction%)" -ForegroundColor Green
        }
        catch {
            Write-Host "❌ Erro ao comprimir: $FilePath - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

# Função para otimizar diretório
function Optimize-Directory {
    param([string]$DirectoryPath)
    
    if (Test-Path $DirectoryPath) {
        Write-Host "🔍 Otimizando diretório: $DirectoryPath" -ForegroundColor Cyan
        
        # Comprimir CSS
        Get-ChildItem -Path $DirectoryPath -Filter "*.css" -Recurse | ForEach-Object {
            Compress-File $_.FullName
        }
        
        # Comprimir JS
        Get-ChildItem -Path $DirectoryPath -Filter "*.js" -Recurse | ForEach-Object {
            Compress-File $_.FullName
        }
        
        Write-Host "✅ Diretório otimizado: $DirectoryPath" -ForegroundColor Green
    }
}

# Verificar se o diretório build existe
if (Test-Path "public/build/assets") {
    Write-Host "🎯 Otimizando assets..." -ForegroundColor Magenta
    Optimize-Directory "public/build/assets"
}

Write-Host "🎉 Otimização concluída! Site voando alto! 🚀" -ForegroundColor Green
