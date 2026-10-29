<?php

declare(strict_types=1);

namespace Tests\Integracao;

use App\MultaRepositorio;
use mysqli;
use PHPUnit\Framework\TestCase;

/**
 * Teste de INTEGRACAO: calculo e registro de multas contra um MySQL real,
 * com o esquema de bibliotecaV2.sql.
 *
 * ATENCAO: os dados das tabelas sao APAGADOS a cada teste. Por isso so roda
 * com TESTES_INTEGRACAO=1 (no CI, o banco e um container descartavel).
 * Conexao pelas mesmas variaveis do sistema: DB_HOST, DB_USER, DB_PASS,
 * DB_NAME e DB_PORT.
 */
final class MultaRepositorioIntegracaoTest extends TestCase
{
    private mysqli $con;
    private MultaRepositorio $repositorio;

    protected function setUp(): void
    {
        if (getenv('TESTES_INTEGRACAO') !== '1') {
            $this->markTestSkipped('Defina TESTES_INTEGRACAO=1 para rodar contra um banco de teste.');
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->con = new mysqli(
            getenv('DB_HOST') ?: '127.0.0.1',
            getenv('DB_USER') ?: 'root',
            getenv('DB_PASS') ?: '',
            getenv('DB_NAME') ?: 'biblioteca',
            (int) (getenv('DB_PORT') ?: '3306')
        );
        $this->repositorio = new MultaRepositorio($this->con);

        $this->limparBanco();
        $this->popularBanco();
    }

    protected function tearDown(): void
    {
        if (isset($this->con)) {
            $this->con->close();
        }
    }

    private function limparBanco(): void
    {
        $this->con->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['multas', 'emprestimos', 'livros', 'usuarios'] as $tabela) {
            $this->con->query("TRUNCATE TABLE $tabela");
        }
        $this->con->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Cenario (datas relativas a hoje):
     *  1 - Ana, prazo ha 4 dias, nao devolvido  -> ATRASADO (4 dias = R$ 3,00)
     *  2 - Ana, prazo daqui 3 dias              -> em dia
     *  3 - Ana, prazo ha 10 dias, ja devolvido  -> sem multa
     *  4 - Bia, prazo ha 2 dias, nao devolvido  -> ATRASADO (2 dias = R$ 1,50)
     *  5 - Bia, prazo vence hoje                -> em dia (borda)
     */
    private function popularBanco(): void
    {
        $this->con->query(
            "INSERT INTO usuarios (id, nome, email, senha, tipo) VALUES
                (1, 'Ana', 'ana@teste.com', 'x', 'usuario'),
                (2, 'Bia', 'bia@teste.com', 'x', 'usuario')"
        );
        $this->con->query(
            "INSERT INTO livros (id, titulo) VALUES (1, 'Livro A'), (2, 'Livro B'), (3, 'Livro C'), (4, 'Livro D'), (5, 'Livro E')"
        );
        $this->con->query(
            "INSERT INTO emprestimos (id, usuario_id, livro_id, data_retirada, data_devolucao_prevista, data_devolucao) VALUES
                (1, 1, 1, CURDATE() - INTERVAL 11 DAY, CURDATE() - INTERVAL 4 DAY,  NULL),
                (2, 1, 2, CURDATE() - INTERVAL 4 DAY,  CURDATE() + INTERVAL 3 DAY,  NULL),
                (3, 1, 3, CURDATE() - INTERVAL 17 DAY, CURDATE() - INTERVAL 10 DAY, CURDATE() - INTERVAL 9 DAY),
                (4, 2, 4, CURDATE() - INTERVAL 9 DAY,  CURDATE() - INTERVAL 2 DAY,  NULL),
                (5, 2, 5, CURDATE() - INTERVAL 7 DAY,  CURDATE(),                   NULL)"
        );
    }

    /** @return list<array{emprestimo_id: int, valor: string, motivo: string, paga: int}> */
    private function multasGravadas(): array
    {
        /** @var list<array{emprestimo_id: int, valor: string, motivo: string, paga: int}> */
        return $this->con
            ->query('SELECT emprestimo_id, valor, motivo, paga FROM multas ORDER BY emprestimo_id')
            ->fetch_all(MYSQLI_ASSOC);
    }

    // Positivo: so os emprestimos vencidos e nao devolvidos geram multa
    public function testEncontraApenasEmprestimosAtrasados(): void
    {
        $atrasos = $this->repositorio->buscarAtrasos();

        $this->assertSame([
            ['id_emprestimo' => 1, 'dias_atraso' => 4, 'valor_multa' => 3.0, 'multa_existente' => false],
            ['id_emprestimo' => 4, 'dias_atraso' => 2, 'valor_multa' => 1.5, 'multa_existente' => false],
        ], $atrasos);
    }

    // Positivo: filtrando por usuario, so vem as multas dele
    public function testFiltraAtrasosPorEmailDoUsuario(): void
    {
        $atrasos = $this->repositorio->buscarAtrasos('bia@teste.com');

        $this->assertSame([4], array_column($atrasos, 'id_emprestimo'));
    }

    // Negativo: usuario inexistente nao tem multas
    public function testEmailInexistenteNaoRetornaMultas(): void
    {
        $this->assertSame([], $this->repositorio->buscarAtrasos('ninguem@teste.com'));
    }

    // Negativo/seguranca: tentativa de SQL injection no e-mail nao vaza dados
    public function testEmailComSqlInjectionNaoRetornaMultas(): void
    {
        $this->assertSame([], $this->repositorio->buscarAtrasos("x' OR '1'='1"));
    }

    // Positivo: registrar grava uma multa nova, nao paga, por emprestimo atrasado
    public function testRegistrarInsereMultasNoBanco(): void
    {
        $this->repositorio->registrar($this->repositorio->buscarAtrasos());

        $this->assertEquals([
            ['emprestimo_id' => 1, 'valor' => '3.00', 'motivo' => 'atraso', 'paga' => 0],
            ['emprestimo_id' => 4, 'valor' => '1.50', 'motivo' => 'atraso', 'paga' => 0],
        ], $this->multasGravadas());
    }

    // Negativo: no mesmo dia a multa nao e recalculada nem duplicada
    public function testNaoRecalculaMultaJaAtualizadaHoje(): void
    {
        $this->repositorio->registrar($this->repositorio->buscarAtrasos());

        $this->assertSame([], $this->repositorio->buscarAtrasos());
        $this->assertCount(2, $this->multasGravadas());
    }

    // Positivo: multa de dias anteriores e ATUALIZADA (nao cria outra linha)
    public function testAtualizaMultaExistenteDeDiaAnterior(): void
    {
        $this->con->query(
            "INSERT INTO multas (valor, motivo, paga, emprestimo_id, ultima_atualizacao)
             VALUES (2.25, 'atraso', 0, 1, NOW() - INTERVAL 1 DAY)"
        );

        $atrasos = $this->repositorio->buscarAtrasos('ana@teste.com');
        $this->assertTrue($atrasos[0]['multa_existente']);

        $this->repositorio->registrar($atrasos);

        $this->assertEquals(
            [['emprestimo_id' => 1, 'valor' => '3.00', 'motivo' => 'atraso', 'paga' => 0]],
            $this->multasGravadas()
        );
    }
}
