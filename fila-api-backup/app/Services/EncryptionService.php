<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptionService
{
    /**
     * Criptografa dados
     *
     * @param mixed $data
     * @return string|null
     */
    public function encrypt($data): ?string
    {
        if (empty($data)) {
            return null;
        }

        try {
            return Crypt::encryptString(
                is_string($data) ? $data : json_encode($data)
            );
        } catch (\Exception $e) {
            Log::error('Erro ao criptografar dados', [
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Descriptografa dados
     *
     * @param string|null $encryptedData
     * @param bool $asJson Indica se o resultado deve ser decodificado como JSON
     * @return mixed
     */
    public function decrypt(?string $encryptedData, bool $asJson = false)
    {
        if (empty($encryptedData)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($encryptedData);
            
            if ($asJson) {
                return json_decode($decrypted, true);
            }
            
            return $decrypted;
        } catch (DecryptException $e) {
            Log::error('Erro ao descriptografar dados', [
                'exception' => $e->getMessage(),
            ]);
            return null;
        }
    }
} 