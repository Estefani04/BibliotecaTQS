<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="../css/estilo.css">
    </head>

    <body>

        <!-- Menu com botões de navegação do sistema -->
        <nav class="menu">
        <form method='POST'>

            <span class="marca">Biblioteca</span>

            <!-- Navegação para as principais páginas do sistema -->
            <button type='submit' name='btn_home'>Menu Principal</button> 
            <button type='submit' name='btn_listar_livros'>Acervo de livros</button>
            <button type='submit' name='btn_listar_historico'>Histórico de retiradas</button>
            <button type='submit' name='btn_multas'>Minhas multas</button>
            <button type='submit' name='btn_conta_user'>Minha conta</button>

            <!-- Botão de logout (sair do sistema) -->
            <button type='submit' name='btn_logout'>Sair</button> 

        </form>
        </nav>

    </body>
</html>


<?php

// Botão de logout: encerra sessão e redireciona para login
if(isset($_POST["btn_logout"])){

    // Remove a variável de sessão do usuário logado
    unset($_SESSION['status']);

    // Redireciona para página de login
    header("location: ../login.php");
}

// Redireciona para histórico de retiradas
if(isset($_POST["btn_listar_historico"])){

    // Remove sessão (observação: isso força logout ao mudar de página)
    unset($_SESSION['status']);

    header("location: historico_retiradas.php");
}

// Redireciona para listagem de livros
if(isset($_POST["btn_listar_livros"])){

    // Remove sessão (também força logout ao navegar)
    unset($_SESSION['status']);

    header("location: todos_livros.php");
}

// Redireciona para menu principal
if(isset($_POST["btn_home"])){

    // Remove sessão (logout involuntário)
    unset($_SESSION['status']);

    header("location: menu_usuario.php");
}

// Redireciona para página de multas
if(isset($_POST["btn_multas"])){

    // Remove sessão (logout involuntário)
    unset($_SESSION['status']);

    header("location: menu_multas.php");
}

// Redireciona para dados da conta do usuário
if(isset($_POST["btn_conta_user"])){

    // Remove sessão (logout involuntário)
    unset($_SESSION['status']);

    header("location: conta_usuario.php");
}

?>