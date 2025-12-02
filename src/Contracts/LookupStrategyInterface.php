<?php

namespace Cep\CepLookup\Contracts;

interface LookupStrategyInterface
{
    /**
     * Executa a busca local por um CEP.
     *
     * @param string $cep CEP normalizado (8 dígitos)
     * @return array|null Resultado da busca ou null se não encontrado
     */
    public function findLocal(string $cep): ?array;

    /**
     * Retorna o nome da estratégia.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Retorna o tipo de dados que esta estratégia busca.
     *
     * @return string
     */
    public function getType(): string;
}
