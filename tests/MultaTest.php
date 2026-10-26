<?php

declare(strict_types=1);

namespace Tests;

use App\Multa;
use PHPUnit\Framework\TestCase;

/**
 * Testes da regra de multa por atraso.
 *
 * Teste-semente do pipeline: cobre casos positivos e negativos.
 * A Estefani (Pessoa 1) amplia com os demais comportamentos do sistema.
 */
final class MultaTest extends TestCase
{
    // Caso positivo: 5 dias de atraso x R$ 0,75 = R$ 3,75
    public function testCincoDiasDeAtrasoGeraMultaCorreta(): void
    {
        $this->assertSame(3.75, Multa::calcular(5));
    }

    // Caso negativo (borda): devolucao em dia nao gera multa
    public function testDevolucaoEmDiaNaoGeraMulta(): void
    {
        $this->assertSame(0.0, Multa::calcular(0));
    }

    // Caso negativo (borda): devolucao adiantada nao gera multa
    public function testDevolucaoAdiantadaNaoGeraMulta(): void
    {
        $this->assertSame(0.0, Multa::calcular(-3));
    }

    // Permite configurar outro valor por dia
    public function testAceitaValorPorDiaCustomizado(): void
    {
        $this->assertSame(10.0, Multa::calcular(4, 2.5));
    }
}
