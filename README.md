# Cep Lookup Package

## Sobre o CepLookup

A atual package é uma refatoração feita pela IA MANUS sobre o código [lookup](https://git.unifesp.br/caio.bevilacqua/lookup) que desenvolvi para a aplicação SYS_CEP

## Visão Geral

O `cep/lookup` é um pacote Laravel que fornece um serviço robusto e flexível para consulta de CEP. Ele utiliza o padrão **Strategy** para permitir diferentes métodos de busca (logradouro, bairro, localidade) e o padrão **Adapter** para integrar fontes de dados locais (banco de dados) e externas (ViaCEP), com um sistema de fallback inteligente.

## Estrutura do Pacote

O pacote segue a estrutura padrão de pacotes Laravel:

```
cep-lookup-package/
├── src/
│   ├── Contracts/
│   ├── Exceptions/
│   ├── Facades/
│   ├── Factories/
│   ├── Providers/
│   ├── Services/
│   ├── Strategies/
│   └── Traits/
├── config/
│   └── cep-lookup.php
├── composer.json
└── README.md
```

## Instalação

### 1. Requisitos

- PHP >= 7.4
- Laravel >= 7.0 (Testado e compatível com Laravel 7, 8, 9, 10 e 11)

### 2. Instalação via Composer

Se o pacote estivesse publicado no Packagist, a instalação seria feita com o seguinte comando:

```bash
composer require cep/lookup
```

**Como o pacote não está publicado**, você precisará instalá-lo manualmente ou via repositório privado.

#### Instalação Manual (Simulação)

Para simular a instalação, você deve:

1.  **Copiar os Arquivos**: Copie a pasta `src/` e o arquivo `config.php` para um diretório de pacotes no seu projeto (ex: `packages/cep/lookup/`).
2.  **Adicionar ao `composer.json` do seu Projeto**: Adicione o pacote como um repositório local no seu `composer.json` principal:

```json
// composer.json do seu projeto Laravel
"repositories": [
    {
        "type": "path",
        "url": "packages/cep/lookup"
    }
],
"require": {
    // ...
    "cep/lookup": "dev-main"
}
```

3.  **Executar o Composer**:
    ```bash
    composer update
    ```

### 3. Configuração do Laravel

O pacote utiliza o recurso de **Package Discovery** do Laravel, então o `ServiceProvider` e a `Facade` devem ser registrados automaticamente.

#### Publicação do Arquivo de Configuração

Para personalizar as estratégias de busca e provedores, você deve publicar o arquivo de configuração:

```bash
php artisan vendor:publish --tag=cep-lookup-config
```

Isso copiará o arquivo `config/cep-lookup.php` para o diretório `config/` do seu projeto.

#### Conteúdo do `config/cep-lookup.php`

O arquivo de configuração permite mapear as estratégias de busca:

```php
return [
    'default_strategy' => 'logradouro',

    'strategies' => [
        'logradouro' => \Cep\CepLookup\Strategies\LogradouroLookupStrategy::class,
        'bairro' => \Cep\CepLookup\Strategies\BairroLookupStrategy::class,
        'localidade' => \Cep\CepLookup\Strategies\LocalidadeLookupStrategy::class,
    ],

    'providers' => [
        'viacep' => \Cep\CepLookup\Services\ViaCepService::class,
    ],
    
    // ... outras configurações
];
```

## Uso

O pacote é acessado primariamente através da Facade `Cep`.

### Consulta Básica

```php
use Cep\CepLookup\Facades\Cep;

// Consulta usando a estratégia 'logradouro'
$resultado = Cep::lookup('logradouro', '01001-000');

// Consulta usando a estratégia padrão (definida em config)
$resultadoPadrao = Cep::lookup('01001-000'); 

if ($resultado['found']) {
    // Dados encontrados
    $dados = $resultado['data'];
    $fonte = $resultado['source']; // 'local' ou 'external'
}
```

### Injeção de Dependência

Para maior testabilidade, injete a interface:

```php
use Cep\CepLookup\Contracts\CepLookupInterface;

class MeuServico
{
    public function __construct(CepLookupInterface $cepService)
    {
        $this->cepService = $cepService;
    }
    
    // ...
}
```

### Uso do Trait `MergesRelations`

Utilize o trait em seus modelos Eloquent para mesclar coleções de relações:

```php
use Cep\CepLookup\Traits\MergesRelations;

class LogBairro extends Model
{
    use MergesRelations;
    
    // ...
    
    public function getLogradourosAttribute()
    {
        return $this->mergeRelations(['logradourosInicio', 'logradourosFim']);
    }
}
```

## Extensibilidade

O pacote é altamente extensível. Para adicionar uma nova estratégia de busca ou um novo provedor externo, basta criar a classe que implementa a interface correspondente (`LookupStrategyInterface` ou `ExternalProviderInterface`) e registrá-la no arquivo `config/cep-lookup.php`.

---

## Conclusão

O pacote `cep/lookup` fornece uma solução completa e modular para consultas de CEP, seguindo as melhores práticas do ecossistema Laravel.

