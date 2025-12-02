<?php

namespace Cep\CepLookup\Contracts;

interface CepLookupInterface
{
    /**
     * Busca informações de CEP usando uma estratégia específica.
     *
     * @param string $strategy Nome da estratégia de busca
     * @param string $cep CEP a ser consultado
     * @return array Resultado da consulta
     */
    public function lookup(string $strategy, string $cep): array;

    /**
     * Busca informações de bairro por CEP.
     *
     * @param string $cep CEP a ser consultado
     * @return array Resultado da consulta
     */
    public function findBairro(string $cep): array;

    /**
     * Busca informações de localidade por CEP.
     *
     * @param string $cep CEP a ser consultado
     * @return array Resultado da consulta
     */
    public function findLocalidade(string $cep): array;

    /**
     * Busca informações de logradouro por CEP.
     *
     * @param string $cep CEP a ser consultado
     * @return array Resultado da consulta
     */
    public function findLogradouro(string $cep): array;
}
