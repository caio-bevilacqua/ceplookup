<?php

namespace Cep\CepLookup\Strategies;

use Cep\CepLookup\Contracts\LookupStrategyInterface;
use App\Models\LogLogradouro;

class LogradouroLookupStrategy implements LookupStrategyInterface
{
    private LogLogradouro $logradouroModel;

    public function __construct(LogLogradouro $logradouroModel)
    {
        $this->logradouroModel = $logradouroModel;
    }

    /**
     * {@inheritdoc}
     */
    public function findLocal(string $cep): ?array
    {
        $logradouro = $this->logradouroModel
            ->where('cep', $cep)
            ->with(['bairroInicio', 'bairroFim', 'localidade'])
            ->first();

        if ($logradouro) {
            return [
                'logradouro' => $logradouro,
                'related_data' => [
                    'bairro_inicio' => $logradouro->bairroInicio,
                    'bairro_fim' => $logradouro->bairroFim,
                    'localidade' => $logradouro->localidade
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
        return 'logradouro';
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'logradouro';
    }
}
