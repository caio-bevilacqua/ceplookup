<?php

namespace Cep\CepLookup\Strategies;

use Cep\CepLookup\Contracts\LookupStrategyInterface;
use App\Models\LogLogradouro;

class BairroLookupStrategy implements LookupStrategyInterface
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
        //$cep = substr($cep,0,5).'%';

        $logradouro = $this->logradouroModel
            //->where('cep','like', $cep)
            ->where('cep', $cep)
            ->with('bairroInicio')
            ->first();

        if ($logradouro && $logradouro->bairroInicio) {
            return [
                'bairro' => $logradouro->bairroInicio,
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
        return 'bairro';
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'bairro';
    }
}