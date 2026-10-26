<?php

// Exibe o menu do administrador
include "head_admin.php";

// Importa o arquivo responsável pela conexão com o banco de dados
require_once("../conexao.php");

// Cria a conexão com o banco de dados
$con = cria_conexao();

// Consulta SQL para buscar todos os livros cadastrados
$sql = "SELECT * FROM livros";

// Executa a consulta SQL
$resultado = mysqli_query($con, $sql);
?>

<h2>Gerenciamento de Livros</h2>

<!-- Botão para acessar a tela de cadastro de livros -->
<form action="cadastro_livro.php">
    <button type="submit">
        Novo Livro
    </button>
</form>

<br><br>

<!-- Tabela para exibir os livros cadastrados -->
<table border="1">

<tr>
    <!-- Cabeçalho da tabela -->
    <th>Título</th>
    <th>Autor</th>
    <th>Editora</th>
    <th>Disponível</th>
    <th>Ações</th>
</tr>

<?php

// Percorre todos os livros retornados pela consulta
while($l = mysqli_fetch_assoc($resultado)){

    echo "<tr>";

    // Exibe o título do livro
    echo "<td>".$l['titulo']."</td>";

    // Exibe o autor do livro
    echo "<td>".$l['autor']."</td>";

    // Exibe a editora do livro
    echo "<td>".$l['editora']."</td>";

// Exibe a disponibilidade do livro
if($l['disponivel'] == 1){
    echo "<td>Disponível</td>";
}
elseif($l['disponivel'] == 0){
    echo "<td>Emprestado</td>";
}
else{
    echo "<td>Inativo</td>";
}

    // Link para editar o livro
    echo "<td>";
    echo "<a href='editar_livro.php?id=".$l['id']."'>Editar</a>";
    echo "</td>";

    echo "</tr>";
}
?>

</table>