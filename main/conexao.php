<?php

/**
 * Realiza a conexao com o banco de dados.
 *
 * As credenciais vem de VARIAVEIS DE AMBIENTE e nunca ficam no repositorio
 * (requisito de seguranca do trabalho). Veja o arquivo .env.example para os
 * valores esperados. Em producao/CI, as variaveis sao definidas pela plataforma.
 */
function cria_conexao(){
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $nome = getenv('DB_NAME') ?: 'biblioteca';
    $port = (int) (getenv('DB_PORT') ?: '3306');

    // Realiza a conexao com o banco de dados
    $con = mysqli_connect($host, $user, $pass, $nome, $port);

    // Verifica se houve erro na conexao
    if (mysqli_connect_errno()) {
        echo "Falhou devido a conexao com Mysql:" . mysqli_connect_error();
    }
    return $con;
}
?>
