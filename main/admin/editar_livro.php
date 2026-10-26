<?php

// Exibe o menu da área administrativa
include "head_admin.php";

// Importa o arquivo de conexão com o banco de dados
require_once("../conexao.php");

// Cria a conexão com o banco
$con = cria_conexao();

// Recebe o ID do livro enviado pela URL
$id = $_GET['id'];

// Verifica se o botão "Salvar Alterações" foi clicado
if(isset($_POST['btn_salvar'])){

    // Recebe os dados informados no formulário
    $titulo = $_POST['titulo'];
    $autor = $_POST['autor'];
    $editora = $_POST['editora'];

    // Atualiza os dados do livro no banco de dados
    $sql = "
    UPDATE livros
    SET
    titulo = '$titulo',
    autor = '$autor',
    editora = '$editora'
    WHERE id = '$id'
    ";

    // Executa a atualização
    mysqli_query($con, $sql);

    // Exibe mensagem de sucesso
    echo "<p style='color:green'>
    Livro atualizado com sucesso!
    </p>";
}

// Verifica se o botão "Inativar Livro" foi clicado
if(isset($_POST['btn_inativar'])){

    // Altera o status do livro para inativo
    $sql = "
    UPDATE livros
    SET disponivel = 2
    WHERE id = '$id'
    ";

    // Executa a atualização
    mysqli_query($con, $sql);

    // Retorna para a listagem de livros
    header("location: livros.php");
}

// Busca os dados atuais do livro
$sql = "SELECT *
FROM livros
WHERE id = '$id'
";

// Executa a consulta
$resultado = mysqli_query($con, $sql);

// Armazena os dados do livro em um array
$livro = mysqli_fetch_assoc($resultado);

?>

<!-- Título da página -->
<h2>Editar Livro</h2>

<!-- Formulário de edição -->
<form method="POST">

<!-- Campo título -->
Título:<br>
<input
type="text"
name="titulo"
value="<?php echo $livro['titulo']; ?>"
required>
<br><br>

<!-- Campo autor -->
Autor:<br>
<input
type="text"
name="autor"
value="<?php echo $livro['autor']; ?>"
required>
<br><br>

<!-- Campo editora -->
Editora:<br>
<input
type="text"
name="editora"
value="<?php echo $livro['editora']; ?>"
required>
<br><br>

<!-- Botão para salvar as alterações -->
<button type="submit" name="btn_salvar">
    Salvar Alterações
</button>

<!-- Botão para inativar o livro -->
<button type="submit" name="btn_inativar">
    Inativar Livro
</button>

<!-- Botão para voltar à página de livros -->
<button
type="button"
onclick="window.location.href='livros.php'">
    Voltar
</button>

</form>