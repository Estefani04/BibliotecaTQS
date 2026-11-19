<html>

<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body class="pagina-login">

<!-- Formulário de login enviado via método POST -->
<form method="POST">
    <h1>Biblioteca</h1>
    <p class="subtitulo">Entre com sua conta para continuar</p>

    <!-- Campo para informar o usuário (e-mail) -->
    <label for='email'>Usuario</label>
    <input type="text" name="usuario" required>

    <!-- Campo para informar a senha -->
    <label for='senha'>Senha</label>
    <input type="password" name="senha" required> <br>

    <!-- Seleção do tipo de usuário -->
    <div class="tipos">
        <input type="radio" id="tipo_admin" name="tipoUsusario" value="admin" required/>
        <label for="tipo_admin">Administrador</label>
        <input type="radio" id="tipo_usuario" name="tipoUsusario" value="usuario" />
        <label for="tipo_usuario">Usuário Padrão</label>
    </div>

    <!-- Botão para enviar o formulário -->
    <button type='submit' name='btn_login' class='principal'>Entrar</button>
</form>

</body>
</html>

<?php
// Inicia ou recupera a sessão atual
session_start();

// Importa funções auxiliares utilizadas pelo sistema
require_once "calcula_multas.php";
require_once "conexao.php";

// Verifica se o botão de login foi pressionado
if (isset($_POST["btn_login"])) {

    // Recebe o usuário informado no formulário
    $USER = $_POST["usuario"];

    // Gera o hash SHA-256 da senha informada
    $SENHA = hash('sha256', (string)$_POST["senha"]);

    // Recebe o tipo de usuário selecionado
    $tipoUsuario = $_POST["tipoUsusario"];

    // Cria a conexão com o banco de dados pela função
    $con = cria_conexao();

    // Consulta para verificar se existe um usuário com os dados informados
    $sql = "SELECT email from usuarios where email='$USER' 
        and senha='$SENHA' and tipo = '$tipoUsuario'";

    // Executa a consulta SQL
    $exesql = mysqli_query($con, $sql);

    // Fecha a conexão com o banco de dados
    mysqli_close($con);

    // Conta quantas linhas foram retornadas pela consulta
    $num_linhas = mysqli_num_rows($exesql);

    // Se encontrar exatamente um usuário, o login é válido
    if ($num_linhas == 1) {

        // Armazena o usuário na sessão
        $_SESSION['status'] = $USER;

        // Exibe mensagem de sucesso
        echo "Usuario logado com sucesso";
        
        // Verifica se o usuário ainda está utilizando a senha padrão
        if($SENHA == hash('sha256',"1234")) {
        // Redireciona para a página de alteração de senha
        header("location: usuario/primeiro_login.php");

        }
        // Se for administrador, redireciona para o menu de administrador
        elseif ($tipoUsuario == "admin") {
            // Calcula a multa atualizada do de todos os usuarios
            $multa_atualizada = calcula_multa();

            // Atualiza a multa no banco de dados
            atualiza_multa($multa_atualizada);

            // Direciona para o menu admin
            header("location: admin/menu_admin.php");
        } 
        // Caso contrário, redireciona para o menu do usuário comum
        else {
            
            // Calcula a multa atualizada do usuário
            $multa_atualizada = calcula_multa($USER);

            // Atualiza a multa no banco de dados
            atualiza_multa($multa_atualizada);

            // Direciona pro menu de usuario
            header("location: usuario/menu_usuario.php");
        }

    } else {

        // Exibe mensagem caso o usuário não seja encontrado
        echo "Usuario não encontrado.";
    }
}
?>