<?php

namespace Cep\CepLookup\Providers;

use Illuminate\Support\ServiceProvider;
use Cep\CepLookup\Contracts\CepLookupInterface;
use Cep\CepLookup\Contracts\ExternalProviderInterface;
use Cep\CepLookup\Services\CepLookupService;
use Cep\CepLookup\Services\ViaCepService;
use Cep\CepLookup\Factories\StrategyFactory;
use Cep\CepLookup\Facades\Cep;
use App\Models\LogBairro;
use App\Models\LogLocalidade;
use App\Models\LogLogradouro;

class CepServiceProvider extends ServiceProvider
{
    /**
     * Registra os serviços no container.
     *
     * @return void
     */
    public function register()
    {
        // Registra o provedor externo (ViaCEP)
        $this->app->bind(ExternalProviderInterface::class, function ($app) {
            return new ViaCepService(
                $app->make(LogLogradouro::class),
                $app->make(LogLocalidade::class)                
            );
        });
        
       // Registra a factory de estratégias
        $this->app->singleton(StrategyFactory::class, function ($app) {
            return new StrategyFactory($app);
        });

       // Registra as estratégias individuais
        $this->registerStrategies();

       // Registra o serviço principal
        $this->app->bind(CepLookupInterface::class, CepLookupService::class);

       // Registra o serviço com um alias para a facade
        $this->app->singleton('cep.ceplookup', function ($app) {
            return $app->make(CepLookupInterface::class);
        });
    }

    /**
     * Executa após todos os serviços serem registrados.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/cep-lookup.php' => config_path('cep-lookup.php'),
        ],'cep-lookup-config');
    }

    /**
     * Registra as estratégias de busca no container.
     *
     * @return void
     */
    private function registerStrategies()
    {

        // Estratégia de busca por bairro
        $this->app->bind(\Cep\CepLookup\Strategies\BairroLookupStrategy::class, function ($app) {
            return new \Cep\CepLookup\Strategies\BairroLookupStrategy(
                $app->make(LogLogradouro::class)
            );
        });

       // Estratégia de busca por localidade
        $this->app->bind(\Cep\CepLookup\Strategies\LocalidadeLookupStrategy::class, function ($app) {
            return new \Cep\CepLookup\Strategies\LocalidadeLookupStrategy(
                $app->make(LogLocalidade::class),
                $app->make(LogLogradouro::class)
            );
        });

        // Estratégia de busca por logradouro
        $this->app->bind(\Cep\CepLookup\Strategies\LogradouroLookupStrategy::class, function ($app) {
            return new \Cep\CepLookup\Strategies\LogradouroLookupStrategy(
                $app->make(LogLogradouro::class)
            );
        });
    }

    /**
     * Retorna os serviços fornecidos por este provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'cep.ceplookup',
            CepLookupInterface::class,
            ExternalProviderInterface::class,
            StrategyFactory::class,
        ];
    }
}
