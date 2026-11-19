<?php

declare(strict_types=1);

namespace App;

/**
 * Regras de negocio de multa por atraso na devolucao de livros.
 *
 * Esta classe isola a regra que, no sistema original, estava embutida
 * em SQL (DATEDIFF(...) * 0.75), permitindo verifica-la por testes.
 */
final class Multa
{
    /** Valor cobrado por dia de atraso (R$). */
    public const VALOR_POR_DIA = 0.75;

    /**
     * Calcula o valor da multa a partir dos dias de atraso.
     *
     * Devolucao em dia ou adiantada (atraso <= 0) nao gera multa.
     *
     * @param int   $diasAtraso  Dias de atraso na devolucao.
     * @param float $valorPorDia Valor cobrado por dia (padrao: VALOR_POR_DIA).
     */
    public static function calcular(int $diasAtraso, float $valorPorDia = self::VALOR_POR_DIA): float
    {
        if ($diasAtraso <= 0) {
            return 0.0;
        }

        return round($diasAtraso * $valorPorDia, 2);
    }
}
