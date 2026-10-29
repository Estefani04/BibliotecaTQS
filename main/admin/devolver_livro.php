<?php

// Importa o arquivo de conexão com o banco de dados
require_once("../conexao.php");

// Cria a conexão com o banco
$con = cria_conexao();

// Recebe o ID do empréstimo enviado pela URL
$id = $_GET['id'];

// Busca o ID do livro relacionado ao empréstimo
$sql = "SELECT livro_id
FROM emprestimos
WHERE id = '$id'
";

// Executa a consulta
$resultado = mysqli_query($con, $sql);

// Obtém os dados do empréstimo
$emprestimo = mysqli_fetch_assoc($resultado);

// Armazena o ID do livro
$livro_id = $emprestimo['livro_id'];

// Atualiza o empréstimo registrando a data de devolução
$sql = "UPDATE emprestimos
SET data_devolucao = CURDATE()
WHERE id = '$id'
";

// Executa a atualização
mysqli_query($con, $sql);

// Atualiza o livro para disponível novamente
$sql = "
UPDATE livros
SET disponivel = 1
WHERE id = '$livro_id'
";

// Executa a atualização do livro
mysqli_query($con, $sql);

// Redireciona para a tela de empréstimos
header("location: cadastro_emprestimo.php");

?>