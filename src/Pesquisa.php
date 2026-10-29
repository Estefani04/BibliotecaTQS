<?php

declare(strict_types=1);

namespace App;

/**
 * Regras de pesquisa e ordenacao das listagens (acervo e historico).
 *
 * Extraido de main/usuario/funcoes.php para poder ser verificado por
 * testes automatizados. As paginas continuam chamando as funcoes antigas,
 * que agora apenas delegam para esta classe.
 */
final class Pesquisa
{
    /**
     * Filtra as linhas da tabela cuja coluna contem o valor buscado.
     *
     * A busca e parcial, ignora maiusculas/minusculas (inclusive acentuadas)
     * e espacos nas pontas. Linhas sem a coluna nao entram no resultado.
     *
     * @param  array<int, array<string, mixed>> $tabela
     * @return array<int, array<string, mixed>>
     */
    public static function filtrar(array $tabela, string $coluna, string $valor): array
    {
        $busca = mb_strtolower(trim($valor));
        $filtrada = [];

        foreach ($tabela as $linha) {
            if (!isset($linha[$coluna])) {
                continue;
            }

            $conteudo = mb_strtolower(trim((string) $linha[$coluna]));
            if (str_contains($conteudo, $busca)) {
                $filtrada[] = $linha;
            }
        }

        return $filtrada;
    }

    /**
     * Ordena a tabela em ordem crescente pela coluna informada.
     *
     * @param  array<int, array<string, mixed>> $tabela
     * @return array<int, array<string, mixed>>
     */
    public static function ordenarCrescente(array $tabela, string $coluna): array
    {
        usort($tabela, fn (array $a, array $b): int => ($a[$coluna] ?? null) <=> ($b[$coluna] ?? null));

        return $tabela;
    }

    /**
     * Ordena a tabela em ordem decrescente pela coluna informada.
     *
     * @param  array<int, array<string, mixed>> $tabela
     * @return array<int, array<string, mixed>>
     */
    public static function ordenarDecrescente(array $tabela, string $coluna): array
    {
        usort($tabela, fn (array $a, array $b): int => ($b[$coluna] ?? null) <=> ($a[$coluna] ?? null));

        return $tabela;
    }

    /**
     * Gera a mensagem exibida ao usuario apos uma pesquisa.
     *
     * O termo pesquisado e escapado para evitar injecao de HTML (XSS),
     * ja que vem direto do formulario.
     */
    public static function mensagem(string $objeto, string $pesquisa, int $numLinhas): string
    {
        $objeto = htmlspecialchars($objeto, ENT_QUOTES, 'UTF-8');
        $pesquisa = htmlspecialchars($pesquisa, ENT_QUOTES, 'UTF-8');

        if ($numLinhas < 1) {
            return "<strong> Não encontramos nenhum Livro com esse(a) $objeto.</strong> Vamos tentar outro(a) $objeto?";
        }

        return "Todos os resultados da pesquisa de '$pesquisa' em $objeto. <br><br>";
    }
}
