<?php

// Importa a conexão com o banco
require_once("../conexao.php");

// Cria conexão com o banco
$con = cria_conexao();

// Recebe o ID da multa
$id = $_GET['id'];

// Alterna entre paga e pendente
$sql = "
UPDATE multas
SET paga = CASE
    WHEN paga = 0 THEN 1
    ELSE 0
END
WHERE id = '$id'
";

// Executa a atualização
mysqli_query($con, $sql);

// Retorna para a tela de multas
header("location: multas.php");

?>