<?php

namespace Cep\CepLookup\Services;

use Cep\CepLookup\Contracts\CepLookupInterface;
use Cep\CepLookup\Contracts\ExternalProviderInterface;
use Cep\CepLookup\Factories\StrategyFactory;
use Cep\CepLookup\Exceptions\InvalidCepException;
use Cep\CepLookup\Exceptions\StrategyNotFoundException;
use Illuminate\Support\Facades\Log;

class CepLookupService implements CepLookupInterface
{
    private StrategyFactory $strategyFactory;
    private ExternalProviderInterface $externalProvider;

    public function __construct(
        StrategyFactory $strategyFactory,
        ExternalProviderInterface $externalProvider)
    {
        $this->strategyFactory = $strategyFactory;
        $this->externalProvider = $externalProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup(string $strategy, string $cep): array
    {
        try {
            $normalizedCep = $this->normalizeCep($cep);
            $this->validateCep($normalizedCep);

            $lookupStrategy = $this->strategyFactory->create($strategy);

            // Tenta busca local primeiro
            $localResult = $lookupStrategy->findLocal($normalizedCep);
            
            if ($localResult) {
                return $this->formatResponse(true, 'local', $localResult, $lookupStrategy->getType());
            }

            // Fallback para provedor externo
            return $this->lookupExternal($normalizedCep);

        } catch (InvalidCepException | StrategyNotFoundException $e) {
            Log::warning('Erro na consulta de CEP: ' . $e->getMessage(), [
                'cep' => $cep,
                'strategy' => $strategy
            ]);
            
            return $this->formatResponse(false, 'error', null, 'error', $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findBairro(string $cep): array
    {
        return $this->lookup('bairro', $cep);
    }

    /**
     * {@inheritdoc}
     */
    public function findLocalidade(string $cep): array
    {
        return $this->lookup('localidade', $cep);
    }

    /**
     * {@inheritdoc}
     */
    public function findLogradouro(string $cep): array
    {
        return $this->lookup('logradouro', $cep);
    }

    /**
     * Normaliza o CEP removendo caracteres não numéricos.
     *
     * @param string $cep
     * @return string
     */
    private function normalizeCep(string $cep): string
    {
        return preg_replace('/\D/', '', $cep);
    }

    /**
     * Valida se o CEP tem o formato correto.
     *
     * @param string $cep
     * @throws InvalidCepException
     */
    private function validateCep(string $cep): void
    {
        if (!preg_match('/^\d{8}$/', $cep)) {
            throw new InvalidCepException("CEP inválido: {$cep}");
        }
    }

    /**
     * Realiza consulta no provedor externo.
     *
     * @param string $cep
     * @return array
     */
    private function lookupExternal(string $cep): array
    {

        if (!$this->externalProvider->isAvailable()) {
            return $this->formatResponse(false, 'viacep', null, 'error','Provedor externo indisponível');
        }

        $result = $this->externalProvider->lookup($cep);
        
        return $this->formatResponse(
            $result['found'] ?? false,
            'viacep',
            $result['data'] ?? null,
            'viacep',
            null,
            $result['metadata'] ?? []
        );
    }

    /**
     * Formata a resposta padronizada.
     *
     * @param bool $found
     * @param string $source
     * @param mixed $data
     * @param string $type
     * @param string|null $error
     * @param array $metadata
     * @return array
     */
    private function formatResponse(
        bool $found,
        string $source,
        $data = null,
        string $type = 'unknown',
        ?string $error = null,
        array $metadata = []
    ): array {
        $response = [
            'found' => $found,
            'source' => $source,
            'type' => $type,
            'data' => $data,
            'timestamp' => now()->toISOString()
        ];

        if ($error) {
            $response['error'] = $error;
        }

        if (!empty($metadata)) {
            $response['metadata'] = $metadata;
        }

        return $response;
    }
}
