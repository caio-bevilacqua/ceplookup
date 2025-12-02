<?php

namespace Cep\CepLookup\Facades;

use Illuminate\Support\Facades\Facade;  

/**
 * @method static array lookup(string $strategy, string $cep)
 * @method static array findBairro(string $cep)
 * @method static array findLocalidade(string $cep)
 * @method static array findLogradouro(string $cep)
 * 
 * @see \Cep\CepLookup\Services\CepLookupService
 */
class Cep extends Facade
{
    /**
     * Retorna o identificador do serviço no container.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cep.ceplookup';
    }
}
