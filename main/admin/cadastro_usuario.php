<?php

// Exibe o menu do administrador
include "head_admin.php";

// Importa o arquivo responsável pela conexão com o banco
require_once("../conexao.php");

// Verifica se o botão "Cadastrar Usuário" foi pressionado
if(isset($_POST['btn_cadastrar'])){

    // Cria a conexão com o banco de dados
    $con = cria_conexao();

    // Recebe os dados informados no formulário
    $nome = $_POST['nome'];
    $cpf = $_POST['cpf'];
    $data_nascimento = $_POST['data_nascimento'];
    $endereco = $_POST['endereco'];
    $email = $_POST['email'];
    $tipo = $_POST['tipo'];

    // Define a senha padrão do usuário
    // No primeiro acesso o sistema solicitará a troca da senha
    $senha = hash('sha256', "1234");

    // Comando SQL para inserir um novo usuário no banco de dados
    $sql = "INSERT INTO usuarios
    (nome, cpf, data_nascimento, endereco, email, senha, tipo)
    VALUES
    ('$nome','$cpf','$data_nascimento','$endereco',
    '$email','$senha','$tipo')";

    // Executa o comando SQL
    if(mysqli_query($con, $sql)){

        // Exibe mensagem de sucesso
        echo "<p style='color:green'>
        Usuário cadastrado com sucesso!<br>
        Senha inicial: 1234
        </p>";

    } else {

        // Exibe mensagem de erro
        echo "<p style='color:red'>
        Erro ao cadastrar usuário.
        </p>";
    }

    // Fecha a conexão com o banco de dados
    mysqli_close($con);
}
?>

<h2>Cadastro de Usuário</h2>

<form method="POST">

    <!-- Campo para informar o nome -->
    Nome:<br>
    <input type="text" name="nome" required>
    <br><br>

    <!-- Campo para informar o CPF -->
    CPF:<br>
    <input type="text" name="cpf" required>
    <br><br>

    <!-- Campo para informar a data de nascimento -->
    Data de Nascimento:<br>
    <input type="date" name="data_nascimento" required>
    <br><br>

    <!-- Campo para informar o endereço -->
    Endereço:<br>
    <input type="text" name="endereco" required>
    <br><br>

    <!-- Campo para informar o e-mail -->
    Email:<br>
    <input type="email" name="email" required>
    <br><br>

    <!-- Campo para selecionar o tipo de usuário -->
    Tipo:<br>
    <select name="tipo" required>
        <option value="usuario">Usuário</option>
        <option value="admin">Administrador</option>
    </select>

    <br><br>

    <!-- Botão responsável por enviar o formulário -->
    <button type="submit" name="btn_cadastrar">
        Cadastrar Usuário
    </button>

    <!-- Botão para cancelar a operação e voltar para a página de usuários -->
    <button
type="button"
onclick="window.location.href='usuarios.php'">
    Cancelar
</button>

</form>