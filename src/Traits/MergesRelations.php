<?php

namespace Cep\CepLookup\Traits;

use Illuminate\Support\Collection;

trait MergesRelations
{
    /**
     * Mescla múltiplas relações em uma única coleção, removendo duplicatas.
     *
     * @param array $relationNames Nomes das relações a serem mescladas.
     * @return Collection
     */
    public function mergeRelations(array $relationNames): Collection
    {
        $merged = collect($relationNames)->reduce(function (Collection $carry, string $relation) {
            if (!method_exists($this, $relation)) {
                // Log de aviso se a relação não existir
                \Log::warning("Relação '{$relation}' não existe no modelo " . get_class($this));
                return $carry;
            }

            $relatedItems = $this->relationLoaded($relation) 
                ? $this->$relation 
                : $this->$relation()->get();

            return $carry->merge($relatedItems);
        }, collect());

        return $merged->unique($this->getKeyName())->values();
    }

    /**
     * Mescla relações específicas com filtros customizados.
     *
     * @param array $relationConfigs Array de configurações de relação
     * @return Collection
     */
    public function mergeRelationsWithFilters(array $relationConfigs): Collection
    {
        $merged = collect($relationConfigs)->reduce(function (Collection $carry, array $config) {
            $relation = $config['relation'];
            $filter = $config['filter'] ?? null;

            if (!method_exists($this, $relation)) {
                \Log::warning("Relação '{$relation}' não existe no modelo " . get_class($this));
                return $carry;
            }

            $relatedItems = $this->relationLoaded($relation) 
                ? $this->$relation 
                : $this->$relation()->get();

            // Aplica filtro se fornecido
            if ($filter && is_callable($filter)) {
                $relatedItems = $relatedItems->filter($filter);
            }

            return $carry->merge($relatedItems);
        }, collect());

        return $merged->unique($this->getKeyName())->values();
    }

    /**
     * Conta o total de itens em múltiplas relações (sem duplicatas).
     *
     * @param array $relationNames
     * @return int
     */
    public function countMergedRelations(array $relationNames): int
    {
        return $this->mergeRelations($relationNames)->count();
    }
}
