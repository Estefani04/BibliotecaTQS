<?php
// Inclui arquivo que gera a conexão
include "../conexao.php";

// Regras de pesquisa/ordenação (testadas automaticamente em tests/)
require_once __DIR__ . "/../../src/Pesquisa.php";

use App\Pesquisa;

/**
 * Filtra uma tabela (array) por busca textual
 */
function pesquisa($tabela, $coluna, $valor){
    return Pesquisa::filtrar($tabela, (string) $coluna, (string) $valor);
}

/**
 * Ordena tabela em ordem crescente por coluna
 */
function ordenaCrescente($tabela, string $coluna){
    return Pesquisa::ordenarCrescente($tabela, $coluna);
}

/**
 * Ordena tabela em ordem decrescente por coluna
 */
function ordenaDecrescente($tabela, string $coluna){
    return Pesquisa::ordenarDecrescente($tabela, $coluna);
}

/**
 * Gera mensagem de retorno para pesquisas
 */
function mensagem_pesquisa($objeto,$pesquisa, $num_linhas){
    return Pesquisa::mensagem((string) $objeto, (string) $pesquisa, (int) $num_linhas);
}

/**
 * Executa update genérico no banco e retorna sucesso/falha
 */
function atualizar_dado($sql){

    // Conexão com banco pela função
    $con = cria_conexao();

    // Executa query
    mysqli_query($con, $sql);

    // Verifica quantas linhas foram afetadas
    $linhas = mysqli_affected_rows($con);

    // Fecha conexão
    mysqli_close($con);
    
    // Retorna true se houve alteração no banco
    if($linhas > 0){
        return true;
    }
    else {
        return false;
    }
}

?>