<?php

namespace Cep\CepLookup\Contracts;

interface ExternalProviderInterface
{
    /**
     * Consulta informações de CEP em um provedor externo.
     *
     * @param string $cep CEP normalizado (8 dígitos)
     * @return array Resultado da consulta
     */
    public function lookup(string $cep): array;

    /**
     * Retorna o nome do provedor.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Verifica se o provedor está disponível.
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
