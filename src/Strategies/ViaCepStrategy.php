<?php

namespace App\Strategies;

use App\Interfaces\CepStrategyInterface;
use App\DTO\StandardAddressDto;
use Illuminate\Support\Facades\Http;

class ViaCepStrategy implements CepStrategyInterface
{
    public function buscar(string $termo): array
    {
        $cep = preg_replace('/\D/', '', $termo);

        if (strlen($cep) !== 8) {
            return [];
        }

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->failed() || isset($response->json()['erro'])) {
            return [];
        }

        $data = $response->json();

        // Mapeamento do ViaCep (JSON) para o DTO padrão
        return [
            new StandardAddressDto(
                cep: $data['cep'],
                logradouro: $data['logradouro'],
                complemento: $data['complemento'],
                bairro: $data['bairro'],
                localidade: $data['localidade'],
                uf: $data['uf'],
                origem: 'VIACEP_API'
            )
        ];
    }
}
