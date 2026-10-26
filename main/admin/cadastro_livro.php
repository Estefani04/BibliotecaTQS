<?php

// Exibe o menu do administrador
include "head_admin.php";

// Importa o arquivo da conexão com o banco de dados
require_once("../conexao.php");

// Verifica se o botão "Cadastrar Livro" foi pressionado
if(isset($_POST['btn_cadastrar'])){

    // Cria a conexão com o banco de dados
    $con = cria_conexao();

    // Recebe os dados informados no formulário
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $editora = $_POST['editora'];
    $edicao = $_POST['edicao'];
    $ano_publicacao = $_POST['ano_publicacao'];
    $isbn = $_POST['isbn'];
    $classificacao = $_POST['classificacao'];
    $numero_paginas = $_POST['numero_paginas'];
    $assunto = $_POST['assunto'];

    // Define que o livro recém-cadastrado estará disponível
    $disponivel = 1;

    // Comando SQL para inserir o livro no banco de dados
    $sql = "INSERT INTO livros
    (titulo, autor, editora, edicao, ano_publicacao,
    isbn, classificacao, numero_paginas, assunto, disponivel)
    VALUES
    ('$titulo','$autor','$editora','$edicao','$ano_publicacao',
    '$isbn','$classificacao','$numero_paginas','$assunto','$disponivel')";

    // Executa o comando SQL
    if(mysqli_query($con, $sql)){

        // Exibe mensagem de sucesso
        echo "<p style='color:green'>
        Livro cadastrado com sucesso!
        </p>";

    } else {

        // Exibe mensagem de erro
        echo "<p style='color:red'>
        Erro ao cadastrar livro.
        </p>";
    }

    // Fecha a conexão com o banco de dados
    mysqli_close($con);
}
?>

<?php
// Título da página
?>
<h2>Cadastro de Livro</h2>

<?php
// Formulário para cadastro de livros
?>
<form method="POST">

<?php
// Campo para informar o título do livro
?>
Título:<br>
<input type="text" name="titulo" required>
<br><br>

<?php
// Campo para informar o autor do livro
?>
Autor:<br>
<input type="text" name="autor" required>
<br><br>

<?php
// Campo para informar a editora
?>
Editora:<br>
<input type="text" name="editora" required>
<br><br>

<?php
// Campo para informar a edição
?>
Edição:<br>
<input type="text" name="edicao" required>
<br><br>

<?php
// Campo para informar o ano de publicação
?>
Ano de Publicação:<br>
<input type="number" name="ano_publicacao" required>
<br><br>

<?php
// Campo para informar o ISBN
?>
ISBN:<br>
<input type="text" name="isbn" required>
<br><br>

<?php
// Campo para informar a classificação do livro
?>
Classificação:<br>
<input type="text" name="classificacao" required>
<br><br>

<?php
// Campo para informar o número de páginas
?>
Número de Páginas:<br>
<input type="number" name="numero_paginas" required>
<br><br>

<?php
// Campo para informar o assunto do livro
?>
Assunto:<br>
<input type="text" name="assunto" required>
<br><br>

<?php
// Botão para cadastrar o livro
?>
<button type="submit" name="btn_cadastrar">
    Cadastrar Livro
</button>
<!-- Botão para cancelar a operação e voltar para a página de livros -->
<button
type="button"
onclick="window.location.href='livros.php'">
    Cancelar
</button>

</form>