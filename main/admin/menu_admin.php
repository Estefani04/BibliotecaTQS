<?php

// Exibe o menu do administrador
include "head_admin.php";

// Verifica se existe um usuário logado na sessão
// Caso não exista, redireciona para a tela de login
if(!isset($_SESSION['status'])){
    header("location: ../login.php");
}
?>

<!-- Título da página -->
<h2>Área Administrativa</h2>

<!-- Mensagem de boas-vindas -->
<p>
    Bem-vindo ao Sistema de Gerenciamento da Biblioteca!
</p>

<p>
    Nesta área administrativa você pode gerenciar os usuários,
    cadastrar e consultar livros, acompanhar multas e visualizar
    relatórios gerais do sistema.
</p>

<p>
    Utilize o menu acima para acessar as funcionalidades desejadas
    e realizar a administração da biblioteca.
</p>