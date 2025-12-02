<?php

namespace App\Strategies;

use App\Interfaces\CepStrategyInterface;
use App\DTO\StandardAddressDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LocalDatabaseStrategy implements CepStrategyInterface
{
    public function buscar(string $termo): array
    {
        $termoFormatado = Str::upper(trim($termo));
        
        if (is_numeric($termoFormatado)) {
            $termoFormatado = preg_replace('(\W)', '', $termoFormatado);
        } else {
            $termoFormatado = Str::ascii($termoFormatado);
        }

        $cacheKey = 'autocomplete:' . md5($termoFormatado);

        return Cache::remember($cacheKey, now()->addHour(), function () use ($termoFormatado) {
            $resultados = $this->executarQuery($termoFormatado);
            
            return $resultados->map(function ($item) {
                // Mapeamento do nome das colunas da sua View (ex: LOG_NO) para o DTO padrão (ex: logradouro)
                return new StandardAddressDto(
                    cep: $item->CEP,
                    logradouro: $item->LOG_NO,
                    complemento: $item->LOG_COMPLEMENTO ?? '',
                    bairro: $item->BAI_NO,
                    localidade: $item->LOC_NO,
                    uf: $item->UFE_SG,
                    origem: 'LOCAL_DB'
                );
            })->toArray();
        });
    }

    // app/Strategies/LocalDatabaseStrategy.php -> protected function executarQuery(string $termo)

protected function executarQuery(string $termo)
{
    $palavras = preg_split('/\s+/', $termo, -1, PREG_SPLIT_NO_EMPTY);
    
    // CORREÇÃO: Removemos a função TRANSLATE() que o SQLite não suporta.
    // Confiamos que o $termo já está normalizado via Str::ascii() no método buscar().
    $campoBusca = "UPPER(CEP || ' ' || LOG_NO || ' ' || LOG_COMPLEMENTO || ' ' || BAI_NO || ' ' || LOC_NO || ' ' || UFE_SG)";

    // CORREÇÃO: Removemos o CAST(CEP AS VARCHAR2(8)) (específico do Oracle/Postgres).
    $orderSql = <<<SQL
        CASE
            WHEN CEP = ? THEN 1
            WHEN UPPER(LOG_NO) LIKE ? THEN 2
            WHEN UPPER(LOG_NO) LIKE ? OR UPPER(LOG_COMPLEMENTO) LIKE ? THEN 3
            WHEN UPPER(BAI_NO) LIKE ? OR UPPER(LOC_NO) LIKE ? THEN 4
            WHEN UPPER(LOC_NO) LIKE ? OR UPPER(UFE_SG) LIKE ? THEN 5
            ELSE 6
        END
    SQL;

    $termoLike = '%' . $termo . '%';
    
    // Bindings: O primeiro é o CEP exato ($termo), os demais são LIKE ($termoLike)
    $orderBindings = [
        $termo, 
        $termoLike, $termoLike, $termoLike, 
        $termoLike, $termoLike, 
        $termoLike, $termoLike
    ]; 

    $query = DB::table('BUSCA_CEP_MVIEW') 
        ->select('CEP', 'LOG_NO', 'LOG_COMPLEMENTO', 'BAI_NO', 'LOC_NO', 'UFE_SG');

    // ... (restante do código WHERE e ORDER BY permanece o mesmo)
    
    // Garanta que o termo de busca na cláusula WHERE também seja normalizado para a query
    $query->where(function ($q) use ($palavras, $campoBusca) {
        foreach ($palavras as $v) {
            $q->whereRaw("$campoBusca LIKE ?", ["%" . $v . "%"]);
        }
    });

    return $query->orderByRaw($orderSql, $orderBindings)->limit(10)->get();
}

}
