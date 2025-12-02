<?php

namespace Cep\CepLookup\Services;

use Cep\CepLookup\Contracts\ExternalProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\LogLocalidade;
use App\Models\LogLogradouro;
use Throwable;

class ViaCepService implements ExternalProviderInterface
{
    private const BASE_URL = 'https://viacep.com.br/ws';
    private const TIMEOUT = 10;

    private LogLogradouro $logradouroModel;
    private LogLocalidade $localidadeModel;

    public function __construct(LogLogradouro $logradouroModel,LogLocalidade $localidadeModel)
    {
        $this->logradouroModel = $logradouroModel;
        $this->localidadeModel = $localidadeModel;
    }

    /**
     * {@inheritdoc}
     */
    public function lookup(string $cep): array
    {
        try {
            $cep = apenasNumeros($cep);
            $response = Http::timeout(self::TIMEOUT)->get(self::BASE_URL . "/{$cep}/json/");

            if ($response->failed()) {
                Log::warning('ViaCEP HTTP request failed', [
                    'cep' => $cep,
                    'status' => $response->status()
                ]);
                
                return ['found' => false, 'source' => $this->getName(), 'data' => $response->status()];
            }

            $data = $response->json();

            if (isset($data['erro'])) {
                return ['found' => false, 'source' => $this->getName(), 'data' => $data['erro']];
            }

            // Enriquece os dados com informações locais
            $metadata = $this->enrichWithLocalData($data);

            return [
                'found' => true,
                'source' => $this->getName(),
                'data' => $data,
                'metadata' => $metadata
            ];

        } catch (Throwable $e) {
            Log::error('Erro na consulta ViaCEP', [
                'cep' => $cep,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ['found' => false, 'source' => $this->getName(), 'data' => $e->getMessage()];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'ViaCEP';
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get(self::BASE_URL . '/01001000/json/');
            return $response->successful();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Enriquece os dados da ViaCEP com informações do banco local.
     *
     * @param array $viaCepData
     * @return array
     */
    private function enrichWithLocalData(array $viaCepData): array
    {
        $metadata = [
            'local_locality_found' => false,
            'local_locality_id' => null,
            'local_logradouro_found' => false,
            'local_logradouro_data' => null,
        ];

        $localidade = $viaCepData['localidade'] ?? null;
        $uf = $viaCepData['uf'] ?? null;
        $cep = apenasNumeros($viaCepData['cep']) ?? null;

        if ($cep)
        {
            $existingLogradouro = $this->findLocalLogradouro($cep);
            if ($existingLogradouro)
            {
                $metadata['local_logradouro_found'] = true;
                $metadata['local_logradouro_data'] = [
                    'log_nu' => $existingLogradouro->log_nu,
                    'log_no' => $existingLogradouro->log_no,
                    'cep' => $existingLogradouro->cep,
                    'bairro' => $existingLogradouro->bairroInicio ? $existingLogradouro->bairroInicio->bai_no : null,
                    'localidade' => $existingLogradouro->localidade ? $existingLogradouro->localidade->loc_no : null
                ];
            }
        }

        if ($localidade && $uf)
        {
            $existingLocality = $this->findLocalLocality($localidade, $uf);    
            if ($existingLocality) {
                $metadata['local_locality_found'] = true;
                $metadata['local_locality_id'] = $existingLocality->loc_nu;
                $metadata['local_locality_data'] = [
                    'loc_nu' => $existingLocality->loc_nu,
                    'loc_no' => $existingLocality->loc_no,
                    'ufe_sg' => $existingLocality->ufe_sg
                ];
            }
        }

        return $metadata;
    }

    /**
     * Busca uma localidade no banco local.
     *
     * @param string $name
     * @param string $uf
     * @return LogLocalidade|null
     */
    private function findLocalLocality(string $name, string $uf): ?LogLocalidade
    {
        return $this->localidadeModel
            ->whereRaw('UPPER(loc_no) = ?', [mb_strtoupper($name)])
            ->where('ufe_sg', $uf)
            ->first();
    }

    /**
     * Busca uma logradouro no banco local.
     *
     * @param string $name
     * @param string $uf
     * @return LogLogradouro|null
     */
    private function findLocalLogradouro(string $cep): ?LogLogradouro
    {
        return $this->logradouroModel->where('cep',$cep)->orWhere('ncep',$cep)->first();
    }
}
