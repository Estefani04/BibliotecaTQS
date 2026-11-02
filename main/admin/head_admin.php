<?php

// Verifica se já existe uma sessão ativa
// Caso não exista, inicia uma nova sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o botão "Sair" foi pressionado
if(isset($_POST["btn_logout"])){

    // Remove a informação do usuário logado da sessão
    unset($_SESSION['status']);

    // Redireciona para a tela de login
    header("location: ../login.php");
}

// Verifica se o botão "Menu Principal" foi pressionado
if(isset($_POST["btn_home"])){

    // Redireciona para o menu principal do administrador
    header("location: menu_admin.php");
}

// Verifica se o botão "Usuários" foi pressionado
if(isset($_POST["btn_usuarios"])){

    // Redireciona para a tela de gerenciamento de usuários
    header("location: usuarios.php");
}

// Verifica se o botão "Livros" foi pressionado
if(isset($_POST["btn_livros"])){

    // Redireciona para a tela de gerenciamento de livros
    header("location: livros.php");
}

// Verifica se o botão "Relatório Geral" foi pressionado
if(isset($_POST["btn_relatorios"])){

    // Redireciona para a tela de relatórios
    header("location: relatorio_admin.php");
}

// Verifica se o botão "Multas" foi pressionado
if(isset($_POST["btn_multas"])){

    // Redireciona para a tela de multas
    header("location: multas.php");
}

if(isset($_POST["btn_emprestimos"])){

    // Redireciona para a tela de cadastro de empréstimos
    header("location: cadastro_emprestimo.php");
}

?>


<!-- Folha de estilo do sistema -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/estilo.css">

<!-- Menu de navegação do administrador -->
<nav class="menu">
<form method="POST">

    <!-- Botões do menu principal -->
    <span class="marca">Biblioteca · Admin</span>
    <button type="submit" name="btn_home">Menu Principal</button>
    <button type="submit" name="btn_usuarios">Usuários</button>
    <button type="submit" name="btn_livros">Livros</button>
    <button type="submit" name="btn_emprestimos">Empréstimos</button>
    <button type="submit" name="btn_multas">Multas</button>
    <button type="submit" name="btn_relatorios">Relatório Geral</button>
    <button type="submit" name="btn_logout">Sair</button>
</form>
</nav>