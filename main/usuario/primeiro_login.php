<?php

// Importa funções auxiliares (ex: atualização no banco)
include "funcoes.php";

// Inicia ou recupera sessão
session_start();

// Mensagem inicial de orientação ao usuário
$mensagem = "A nova senha deve ter no mínimo 8 caracteres.";

// Verifica se o usuário está logado
if(isset($_SESSION['status']) and $_SESSION['status']!= "" ){

    // Usuário atual (email vindo da sessão)
    $user = $_SESSION['status'];
}
else{

    // Se não estiver logado, redireciona para login
    header("location: ../login.php");
}

/**
 * PROCESSO DE ATUALIZAÇÃO DE SENHA (primeiro login)
 */
if(isset($_POST['btn_atualizar'])){

    // Senhas enviadas pelo formulário
    $senhaAtual = $_POST['senhaAtual'];
    $senhaNova = $_POST['senhaNova'];
    $senhaNovaConfirma = $_POST['senhaNovaConfirma'];

    // Validação de tamanho mínimo da senha
    if(strlen(trim($senhaNova)) < 8){
        $mensagem = "Informe uma senha com mais de oito caracteres.";
    }

    // Validação de confirmação de senha
    elseif($senhaNova != $senhaNovaConfirma){
        $mensagem = "Os dois campos de nova senha e de confirma senha devem ter a mesma senha.";
    }
    else{

        // Gera hash da senha atual e da nova senha
        $senhaAtual = hash('sha256', (string)$senhaAtual);
        $senhaNova = hash('sha256', (string)$senhaNova);

        // Atualiza senha apenas se a senha atual estiver correta
        $sql = "UPDATE usuarios set senha = '$senhaNova' 
                where email = '$user' and senha = '$senhaAtual'";
        
        // Executa atualização
        if(atualizar_dado($sql)){
            $mensagem = "Dados atualizados com sucesso!";
        }
        else {
            $mensagem = "A atualização falhou, tente novamente!";
        }
    }
}

/**
 * RETORNO PARA LOGIN
 */
if(isset($_POST['btn_login'])){

    // Redireciona para página de login
    header("location: ../login.php");
}

?>

<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../css/estilo.css">
    </head>

    <body>

        <br>

        <!-- Botão para voltar ao login -->
        <form method='POST'>
            <p> 
                <button type='submit' name='btn_login'> Voltar para login </button>
            </p>
        </form>

        <!-- Tela de primeiro login -->
        <h2>Primeiro Login</h2>

        <p>
        Como é seu primeiro login, sua senha deve ser redefinida para a segurança da sua conta.
        </p>

        <form method='POST'>
            <div>

                <!-- Exibe usuário logado -->
                <p> <strong> Usuario <?php echo $user;?>.</strong> </p><br>

                <h3> Alterar senha </h3>

                <!-- Campo senha atual -->
                <strong> Senha atual </strong>
                <input type='password' name='senhaAtual' required> <br><br>

                <!-- Campo nova senha -->
                <strong> Nova senha </strong>
                <input type='password' name='senhaNova' required>

                <!-- Confirmação da nova senha -->
                <strong> Confirma nova senha </strong>
                <input type='password' name='senhaNovaConfirma' required><br>

                <!-- Mensagem de validação/erro/sucesso -->
                <p class="mensagem"><?php echo $mensagem;?></p>

                <p> 
                    <!-- Botão de atualização -->
                    <button type='submit' name='btn_atualizar'> Atualizar senha </button>
                </p>

            </div>
        </form>

    </body>
</html>