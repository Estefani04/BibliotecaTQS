<?php

// Exibe o menu do administrador
include "head_admin.php";

// Importa o arquivo responsável pela conexão com o banco de dados
require_once("../conexao.php");

// Cria a conexão com o banco de dados
$con = cria_conexao();

// Consulta SQL para contar o total de usuários cadastrados
$usuarios = mysqli_fetch_assoc(
    mysqli_query($con,"SELECT COUNT(*) total FROM usuarios")
);

// Consulta SQL para contar o total de livros cadastrados
$livros = mysqli_fetch_assoc(
    mysqli_query($con,"SELECT COUNT(*) total FROM livros")
);

// Consulta SQL para contar apenas os empréstimos ativos
$emprestimos = mysqli_fetch_assoc(
    mysqli_query($con,"
    SELECT COUNT(*) total
    FROM emprestimos
    WHERE data_devolucao IS NULL")
);

// Consulta SQL para contar todas as multas cadastradas
$multas = mysqli_fetch_assoc(
    mysqli_query($con,"
    SELECT COUNT(*) total
    FROM multas")
);

// Consulta SQL para contar multas pagas
$multas_pagas = mysqli_fetch_assoc(
    mysqli_query($con,"
    SELECT COUNT(*) total
    FROM multas
    WHERE paga = 1")
);

// Consulta SQL para contar multas pendentes
$multas_pendentes = mysqli_fetch_assoc(
    mysqli_query($con,"
    SELECT COUNT(*) total
    FROM multas
    WHERE paga = 0")
);

// Consulta SQL para somar apenas o valor das multas pendentes
$valor_multas = mysqli_fetch_assoc(
    mysqli_query($con,"
    SELECT IFNULL(SUM(valor),0) total
    FROM multas
    WHERE paga = 0")
);

?>

<h2>Relatório Geral</h2>

<div class="relatorio">

<!-- Exibe a quantidade total de usuários cadastrados -->
<p>Total de usuários<b><?= $usuarios['total'] ?></b></p>

<!-- Exibe a quantidade total de livros cadastrados -->
<p>Total de livros<b><?= $livros['total'] ?></b></p>

<!-- Exibe a quantidade de empréstimos ativos -->
<p>Empréstimos ativos<b><?= $emprestimos['total'] ?></b></p>

<!-- Exibe a quantidade total de multas -->
<p>Total de multas registradas<b><?= $multas['total'] ?></b></p>

<!-- Exibe a quantidade de multas pagas -->
<p>Multas pagas<b><?= $multas_pagas['total'] ?></b></p>

<!-- Exibe a quantidade de multas pendentes -->
<p>Multas pendentes<b><?= $multas_pendentes['total'] ?></b></p>

<!-- Exibe o valor total das multas pendentes -->
<p>Valor total em multas pendentes
<b>R$ <?= number_format($valor_multas['total'], 2, ',', '.') ?></b>
</p>

</div>