<?php

namespace Cep\CepLookup\Strategies;

use Cep\CepLookup\Contracts\LookupStrategyInterface;
use App\Models\LogLocalidade;
use App\Models\LogLogradouro;

class LocalidadeLookupStrategy implements LookupStrategyInterface
{
    private LogLocalidade $localidadeModel;
    private LogLogradouro $logradouroModel;

    public function __construct(LogLocalidade $localidadeModel, LogLogradouro $logradouroModel)
    {
        $this->localidadeModel = $localidadeModel;
        $this->logradouroModel = $logradouroModel;
    }

    /**
     * {@inheritdoc}
     */
    public function findLocal(string $cep): ?array
    {
        // Primeira tentativa: busca direta na tabela de localidades
        $localidade = $this->localidadeModel
            ->where('cep', $cep)
            ->orWhere('ncep', $cep)
            ->first();

        if ($localidade) {
            return [
                'localidade' => $localidade,
                'source_method' => 'via_localidade'
            ];
        }

        // Segunda tentativa: busca via logradouro
        $logradouro = $this->logradouroModel
            ->where('cep', $cep)
            ->with('localidade')
            ->first();

        if ($logradouro && $logradouro->localidade) {
            return [
                'localidade' => $logradouro->localidade,
                'source_method' => 'via_logradouro',
                'logradouro_origem' => [
                    'log_nu' => $logradouro->log_nu,
                    'log_no' => $logradouro->log_no,
                    'cep' => $logradouro->cep
                ]
            ];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'localidade';
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'localidade';
    }
}
