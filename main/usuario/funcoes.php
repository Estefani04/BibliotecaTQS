<?php
// Inclui arquivo que gera a conexão
include "../conexao.php";

/**
 * Filtra uma tabela (array) por busca textual
 */
function pesquisa($tabela, $coluna, $valor){

    $tabela_filtrada = [];

    // Percorre cada linha da tabela
    foreach($tabela as $linha){

        // Busca parcial (case insensitive)
        if(str_contains(
            strtolower(trim($linha[$coluna])),
            strtolower(trim($valor))
        )){
            $tabela_filtrada[] = $linha;
        }
    }

    return $tabela_filtrada;
}

/**
 * Ordena tabela em ordem crescente por coluna
 */
function ordenaCrescente($tabela, string $coluna){

    usort($tabela, function($a, $b) use ($coluna) {
        return $a[$coluna] <=> $b[$coluna];
    });

    return $tabela;
}

/**
 * Ordena tabela em ordem decrescente por coluna
 */
function ordenaDecrescente($tabela, string $coluna){

    usort($tabela, function($a, $b) use ($coluna) {
        return $b[$coluna] <=> $a[$coluna];
    });

    return $tabela;
}

/**
 * Gera mensagem de retorno para pesquisas
 */
function mensagem_pesquisa($objeto,$pesquisa, $num_linhas){

    if($num_linhas < 1){
        $mensagem = "<strong> Não escontramos nenhum Livro com esse(a) $objeto.</strong> Vamos tentar outro(a) $objeto?";
    }
    else {
        $mensagem = "Todos os resultados da pesquisa de '$pesquisa' em $objeto. <br><br>";
    }
    
    return $mensagem;
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