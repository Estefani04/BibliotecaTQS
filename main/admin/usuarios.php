<?php

// Exibe o menu do administrador
include "head_admin.php";

// Importa o arquivo responsável pela conexão com o banco de dados
require_once("../conexao.php");

// Realiza a conexão com o banco de dados pela função
$con = cria_conexao();

// Consulta SQL para buscar os usuários cadastrados
$sql = "SELECT id,nome,email,cpf FROM usuarios";

// Executa a consulta SQL
$resultado = mysqli_query($con,$sql);
?>

<!-- Título da página -->
<h2>Usuários cadastrados</h2>

<!-- Botão para acessar a tela de cadastro de usuários -->
<form action="cadastro_usuario.php">
    <button type="submit">
        Novo Usuário
    </button>
</form>

<br>
<!-- Tabela para exibir os usuários cadastrados -->
<table border="1">

<tr>
    <!-- Cabeçalho da tabela -->
    <th>ID</th>
    <th>Nome</th>
    <th>Email</th>
    <th>CPF</th>
</tr>

<?php

// Percorre todos os usuários retornados pela consulta
while($u = mysqli_fetch_assoc($resultado)){

    echo "<tr>";

    // Exibe o ID do usuário
    echo "<td>".$u['id']."</td>";

    // Exibe o nome do usuário
    echo "<td>".$u['nome']."</td>";

    // Exibe o e-mail do usuário
    echo "<td>".$u['email']."</td>";

    // Exibe o CPF do usuário
    echo "<td>".$u['cpf']."</td>";

    echo "</tr>";
}
?>

</table>