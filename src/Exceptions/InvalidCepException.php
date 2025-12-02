<?php

namespace Cep\CepLookup\Exceptions;

use InvalidArgumentException;

class InvalidCepException extends InvalidArgumentException
{
    public function __construct(string $message = "CEP inválido", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
