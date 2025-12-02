<?php

namespace Cep\CepLookup\Exceptions;

use RuntimeException;

class StrategyNotFoundException extends RuntimeException
{
    public function __construct(string $message = "Estratégia não encontrada", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
