<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações do Serviço de Consulta de CEP
    |--------------------------------------------------------------------------
    |
    | Aqui você pode configurar o comportamento do serviço de busca de CEP.
    |
    */

    'viacep' => [
        // Timeout em segundos para a requisição HTTP ao ViaCEP
        'timeout' => 5.0,
    ],

    'database' => [
        // Define a ordem de busca no banco de dados local
        // As estratégias disponíveis são: 'logradouro', 'localidade', 'bairro'
        // A busca no ViaCEP é sempre o último recurso.
        'strategies_order' => [
            'logradouro',
            'localidade',
            // 'bairro', // Descomente se quiser buscar por bairro
        ],
    ],

    // Define o alias do Facade. O Laravel 7+ usa auto-discovery.
    // 'facade_alias' => 'Cep',
];
