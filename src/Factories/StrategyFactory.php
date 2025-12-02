<?php

namespace Cep\CepLookup\Factories;

use Cep\CepLookup\Contracts\LookupStrategyInterface;
use Cep\CepLookup\Exceptions\StrategyNotFoundException;
use Illuminate\Container\Container;

class StrategyFactory
{
    private Container $container;
    private array $strategies;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->strategies = [
            'logradouro' => \Cep\CepLookup\Strategies\LogradouroLookupStrategy::class,
            'localidade' => \Cep\CepLookup\Strategies\LocalidadeLookupStrategy::class,
            'bairro' => \Cep\CepLookup\Strategies\BairroLookupStrategy::class,
        ];
    }

    /**
     * Cria uma instância da estratégia solicitada.
     *
     * @param string $strategyName
     * @return LookupStrategyInterface
     * @throws StrategyNotFoundException
     */
    public function create(string $strategyName): LookupStrategyInterface
    {
        $strategyName = strtolower($strategyName);

        if (!isset($this->strategies[$strategyName])) {
            throw new StrategyNotFoundException("Estratégia '{$strategyName}' não encontrada.");
        }

        $strategyClass = $this->strategies[$strategyName];
        
        return $this->container->make($strategyClass);
    }

    /**
     * Registra uma nova estratégia.
     *
     * @param string $name
     * @param string $strategyClass
     * @return void
     */
    public function register(string $name, string $strategyClass): void
    {
        $this->strategies[strtolower($name)] = $strategyClass;
    }

    /**
     * Retorna todas as estratégias disponíveis.
     *
     * @return array
     */
    public function getAvailableStrategies(): array
    {
        return array_keys($this->strategies);
    }

    /**
     * Verifica se uma estratégia está registrada.
     *
     * @param string $strategyName
     * @return bool
     */
    public function hasStrategy(string $strategyName): bool
    {
        return isset($this->strategies[strtolower($strategyName)]);
    }
}
