<?php

declare(strict_types=1);

namespace App;

use mysqli;

/**
 * Acesso ao banco para calculo e registro das multas por atraso.
 *
 * Substitui as consultas que estavam em main/calcula_multas.php:
 * - usa prepared statements (o e-mail era concatenado direto no SQL,
 *   abrindo brecha para SQL injection);
 * - o valor da multa passa a ser calculado por Multa::calcular(), em vez
 *   de "DATEDIFF(...) * 0.75" escrito no SQL.
 */
final class MultaRepositorio
{
    public function __construct(private mysqli $con)
    {
    }

    /**
     * Lista os emprestimos atrasados (nao devolvidos e com prazo vencido)
     * cuja multa ainda nao foi atualizada hoje.
     *
     * @param  string|null $email Se informado, considera apenas esse usuario.
     * @return list<array{id_emprestimo: int, dias_atraso: int, valor_multa: float, multa_existente: bool}>
     */
    public function buscarAtrasos(?string $email = null): array
    {
        $sql = "SELECT e.id AS id_emprestimo,
                    DATEDIFF(CURDATE(), DATE(e.data_devolucao_prevista)) AS dias_atraso,
                    m.emprestimo_id IS NOT NULL AS multa_existente
                FROM emprestimos e
                LEFT JOIN usuarios u ON u.id = e.usuario_id
                LEFT JOIN multas m ON m.emprestimo_id = e.id
                WHERE e.data_devolucao IS NULL
                    AND DATE(e.data_devolucao_prevista) < CURDATE()
                    AND (DATE(m.ultima_atualizacao) < CURDATE() OR m.ultima_atualizacao IS NULL)
                    AND (? IS NULL OR u.email = ?)
                ORDER BY e.id";

        $stmt = $this->con->prepare($sql);
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $atrasos = [];
        foreach ($resultado as $linha) {
            $dias = (int) $linha['dias_atraso'];
            $atrasos[] = [
                'id_emprestimo'   => (int) $linha['id_emprestimo'],
                'dias_atraso'     => $dias,
                'valor_multa'     => Multa::calcular($dias),
                'multa_existente' => (bool) $linha['multa_existente'],
            ];
        }
        $stmt->close();

        return $atrasos;
    }

    /**
     * Grava as multas calculadas: atualiza a existente ou cria uma nova.
     *
     * @param list<array{id_emprestimo: int, dias_atraso: int, valor_multa: float, multa_existente: bool}> $atrasos
     */
    public function registrar(array $atrasos): void
    {
        $atualiza = $this->con->prepare(
            "UPDATE multas SET valor = ?, ultima_atualizacao = NOW() WHERE emprestimo_id = ?"
        );
        $insere = $this->con->prepare(
            "INSERT INTO multas (valor, motivo, paga, emprestimo_id, ultima_atualizacao)
             VALUES (?, 'atraso', 0, ?, NOW())"
        );

        foreach ($atrasos as $atraso) {
            $stmt = $atraso['multa_existente'] ? $atualiza : $insere;
            $stmt->bind_param('di', $atraso['valor_multa'], $atraso['id_emprestimo']);
            $stmt->execute();
        }

        $atualiza->close();
        $insere->close();
    }
}
