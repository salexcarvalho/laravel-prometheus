<?php

namespace Eudovic\PrometheusPHP\Services;

class QuantileCalculator
{
    public static function calculate(array $values, array $quantiles): array
    {
        sort($values);
        $results = [];
        $count = count($values);

        foreach ($quantiles as $quantile) {
            $index = (int) ceil($quantile * ($count - 1));
            $index = min($index, $count - 1); // Garante que o índice está dentro dos limites
            $results[] = [
                'quantile' => $quantile,
                'value' => $values[$index] ?? 0,
            ];
        }

        return $results;
    }
}
