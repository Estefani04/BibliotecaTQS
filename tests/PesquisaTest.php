<?php

declare(strict_types=1);

namespace Tests;

use App\Pesquisa;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Testes de comportamento da pesquisa e ordenacao das listagens
 * (tela "Todos os livros" e "Historico de retiradas").
 */
final class PesquisaTest extends TestCase
{
    /** @return array<int, array<string, mixed>> */
    private function acervo(): array
    {
        return [
            ['titulo' => 'Dom Casmurro',          'autor' => 'Machado de Assis',   'ano_publicacao' => 1899],
            ['titulo' => 'O Cortiço',             'autor' => 'Aluísio Azevedo',    'ano_publicacao' => 1890],
            ['titulo' => 'Memórias Póstumas',     'autor' => 'Machado de Assis',   'ano_publicacao' => 1881],
            ['titulo' => 'Capitães da Areia',     'autor' => 'Jorge Amado',        'ano_publicacao' => 1937],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>> $tabela
     * @return list<mixed>
     */
    private function coluna(array $tabela, string $coluna): array
    {
        return array_column($tabela, $coluna);
    }

    // ---------------------------------------------------------------- filtrar

    // Positivo: busca parcial encontra todos os livros do autor
    public function testFiltrarEncontraPorTrechoDoTexto(): void
    {
        $resultado = Pesquisa::filtrar($this->acervo(), 'autor', 'Machado');

        $this->assertSame(['Dom Casmurro', 'Memórias Póstumas'], $this->coluna($resultado, 'titulo'));
    }

    /** @return array<string, array{string}> */
    public static function variacoesDeDigitacao(): array
    {
        return [
            'minusculas'          => ['dom casmurro'],
            'maiusculas'          => ['DOM CASMURRO'],
            'espacos nas pontas'  => ['   Dom Casmurro  '],
        ];
    }

    // Positivo: a busca ignora maiusculas/minusculas e espacos nas pontas
    #[DataProvider('variacoesDeDigitacao')]
    public function testFiltrarIgnoraCaixaEEspacos(string $digitado): void
    {
        $resultado = Pesquisa::filtrar($this->acervo(), 'titulo', $digitado);

        $this->assertSame(['Dom Casmurro'], $this->coluna($resultado, 'titulo'));
    }

    // Positivo: letras acentuadas maiusculas tambem sao encontradas
    // (com strtolower() o "Ç" nao virava "ç" e a busca falhava)
    public function testFiltrarIgnoraCaixaEmLetrasAcentuadas(): void
    {
        $resultado = Pesquisa::filtrar($this->acervo(), 'titulo', 'CORTIÇO');

        $this->assertSame(['O Cortiço'], $this->coluna($resultado, 'titulo'));
    }

    // Positivo: funciona em colunas numericas (ex.: ano)
    public function testFiltrarFuncionaEmColunaNumerica(): void
    {
        $resultado = Pesquisa::filtrar($this->acervo(), 'ano_publicacao', '189');

        $this->assertSame(['Dom Casmurro', 'O Cortiço'], $this->coluna($resultado, 'titulo'));
    }

    // Negativo: termo inexistente retorna lista vazia
    public function testFiltrarSemCorrespondenciaRetornaVazio(): void
    {
        $this->assertSame([], Pesquisa::filtrar($this->acervo(), 'autor', 'Clarice'));
    }

    // Negativo: coluna inexistente nao quebra e nao retorna nada
    public function testFiltrarEmColunaInexistenteRetornaVazio(): void
    {
        $this->assertSame([], Pesquisa::filtrar($this->acervo(), 'editora', 'Globo'));
    }

    // Negativo: tabela vazia continua vazia
    public function testFiltrarTabelaVaziaRetornaVazio(): void
    {
        $this->assertSame([], Pesquisa::filtrar([], 'titulo', 'Dom'));
    }

    // Borda: sem filtro, tudo e mantido
    public function testFiltrarComTextoVazioMantemTodasAsLinhas(): void
    {
        $this->assertCount(4, Pesquisa::filtrar($this->acervo(), 'titulo', ''));
    }

    // -------------------------------------------------------------- ordenacao

    // Positivo: ordena titulos em ordem alfabetica
    public function testOrdenarCrescentePorTexto(): void
    {
        $resultado = Pesquisa::ordenarCrescente($this->acervo(), 'titulo');

        $this->assertSame(
            ['Capitães da Areia', 'Dom Casmurro', 'Memórias Póstumas', 'O Cortiço'],
            $this->coluna($resultado, 'titulo')
        );
    }

    // Positivo: ordena numeros do maior para o menor
    public function testOrdenarDecrescentePorNumero(): void
    {
        $resultado = Pesquisa::ordenarDecrescente($this->acervo(), 'ano_publicacao');

        $this->assertSame([1937, 1899, 1890, 1881], $this->coluna($resultado, 'ano_publicacao'));
    }

    // Propriedade: decrescente e o inverso exato do crescente
    public function testDecrescenteEInversoDoCrescente(): void
    {
        $crescente = Pesquisa::ordenarCrescente($this->acervo(), 'ano_publicacao');
        $decrescente = Pesquisa::ordenarDecrescente($this->acervo(), 'ano_publicacao');

        $this->assertSame(array_reverse($crescente), $decrescente);
    }

    // Negativo: ordenar nao perde nem duplica linhas
    public function testOrdenarMantemTodasAsLinhas(): void
    {
        $resultado = Pesquisa::ordenarCrescente($this->acervo(), 'autor');

        $this->assertCount(4, $resultado);
        $this->assertEqualsCanonicalizing($this->acervo(), $resultado);
    }

    // Negativo: tabela vazia continua vazia
    public function testOrdenarTabelaVaziaRetornaVazio(): void
    {
        $this->assertSame([], Pesquisa::ordenarCrescente([], 'titulo'));
        $this->assertSame([], Pesquisa::ordenarDecrescente([], 'titulo'));
    }

    // --------------------------------------------------------------- mensagem

    // Positivo: com resultados, informa o termo e o campo pesquisado
    public function testMensagemComResultadosCitaOTermo(): void
    {
        $mensagem = Pesquisa::mensagem('autor', 'Machado', 2);

        $this->assertStringContainsString("pesquisa de 'Machado' em autor", $mensagem);
    }

    // Negativo: sem resultados, avisa e sugere tentar outro termo
    public function testMensagemSemResultadosAvisaOUsuario(): void
    {
        $mensagem = Pesquisa::mensagem('titulo', 'xyz', 0);

        $this->assertStringContainsString('Não encontramos nenhum Livro', $mensagem);
        $this->assertStringContainsString('Vamos tentar outro(a) titulo?', $mensagem);
    }

    // Seguranca: HTML digitado na busca e escapado (evita XSS)
    public function testMensagemEscapaHtmlDoTermoPesquisado(): void
    {
        $mensagem = Pesquisa::mensagem('titulo', '<script>alert(1)</script>', 3);

        $this->assertStringNotContainsString('<script>', $mensagem);
        $this->assertStringContainsString('&lt;script&gt;', $mensagem);
    }
}
