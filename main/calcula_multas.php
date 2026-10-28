<?php
// Inclui arquivo que gera a conexão
include "conexao.php";

// Regras de multa (testadas automaticamente em tests/)
require_once __DIR__ . "/../src/Multa.php";
require_once __DIR__ . "/../src/MultaRepositorio.php";

use App\MultaRepositorio;

/**
 * Calcula multas de usuários com base em atrasos
 *
 * - Calcula dias de atraso de cada empréstimo não devolvido
 * - Valor da multa: dias de atraso x R$ 0,75 (App\Multa)
 * - Verifica se já existe multa para o empréstimo
 *
 * Retorna a lista de multas a atualizar, ou null se não houver nenhuma.
 */
function calcula_multa($usuario = null){

    // Conexão com banco de dados pela função
    $con = cria_conexao();

    // Consulta os atrasos (todos os usuários ou só o informado)
    $multas = (new MultaRepositorio($con))->buscarAtrasos($usuario);

    // Fecha conexão
    mysqli_close($con);

    // Retorna resultados se houver multas
    if (count($multas) >= 1){
        return $multas;
    }
    else return null;
}

/**
 * Atualiza ou insere multas no banco de dados
 */
function atualiza_multa($multas){

    // Só executa se houver dados
    if($multas != null){

        // Conexão com banco pela função
        $con = cria_conexao();

        // Atualiza a multa existente ou insere uma nova
        (new MultaRepositorio($con))->registrar($multas);

        // Fecha conexão
        mysqli_close($con);
    }
}
?>
