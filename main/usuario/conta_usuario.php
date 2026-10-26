<?php
// Inclui arquivo que gera a conexão
include "../conexao.php";

// Inclui o cabeçalho padrão do usuário (layout/menu da aplicação)
include "head_usuario.php";

// Inicia ou recupera a sessão
session_start();

// Variáveis de controle
$num_linhas = 0;
$exesql_retirados  = '';

// Verifica se o usuário está autenticado
if(isset($_SESSION['status']) and $_SESSION['status']!= "" ){

    // Usuário logado (email armazenado na sessão)
    $user = $_SESSION['status'];

    // Conexão com o banco de dados pela função
    $con = cria_conexao();

    // Consulta dados do usuário logado
    $sql_retirados = "SELECT 
                nome,
                cpf,
                data_nascimento,
                endereco,
                email
            from usuarios
            where email='$user'";

    // Executa consulta
    $exesql_retirados = mysqli_query($con, $sql_retirados);

    // Fecha conexão com o banco
    mysqli_close($con);

    // Conta quantos registros foram retornados
    $num_linhas = mysqli_num_rows($exesql_retirados);

}
else{

    // Se não estiver logado, redireciona para login
    header("location: ../login.php");
}

// Verifica se o usuário clicou no botão de alteração de dados
if(isset($_POST['btn_alterar'])){

    // Define flags na sessão para controle da tela de alteração
    $_SESSION['nome'] = "alterar";
    $_SESSION['nascimento'] = "alterar";
    $_SESSION['endereco'] = "alterar";
    $_SESSION['email'] = "alterar";
    $_SESSION['senha'] = "alterar";

    // Redireciona para a página de alteração de dados
    header("location: alterar_dados.php");
}

?>

<html lang="pt-br">
    <head>
        <meta charset="utf-8">
    </head>

    <body>

        <br>

        <!-- Título da página -->
        <h2>Informações sobre a sua conta</h2>

        <form method='POST'>
            <div>

                <?php 

                // Se encontrou dados do usuário
                if ($num_linhas >= 1) {

                    echo "<h3>Seus dados cadastrados:</h3>";

                    // Exibe os dados do usuário
                    while ($resul = mysqli_fetch_assoc($exesql_retirados)) {

                        echo "<strong>Nome: </strong>" . $resul['nome']. "<br> <br>";
                        echo "<strong>Data Nascimento: </strong>" . $resul['data_nascimento']. "<br> <br>";
                        echo "<strong>Endereço: </strong>" . $resul['endereco']. "<br> <br>";
                        echo "<strong>Email: </strong>" . $resul['email']. "<br> <br>";
                        echo "<strong>CPF/CNPJ: </strong>" . $resul['cpf']. "<br> <br>";
                    }

                }
                else{

                    // Caso não encontre dados
                    echo "Não encontramos seus dados";
                }

                ?>

                <!-- Botão para redirecionar para edição de dados -->
                <button type='submit' name='btn_alterar'> Atualizar dados </button> 

            </div>
        </form>

    </body>
</html>