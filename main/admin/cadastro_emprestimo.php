<?php

// Exibe o menu da área administrativa
include "head_admin.php";

// Importa o arquivo de conexão e cria a conexão com o banco
require_once("../conexao.php");

$con = cria_conexao();

// Busca todos os usuários que podem pegar livros emprestados
$sql_usuarios = "SELECT id, nome FROM usuarios WHERE tipo = 'usuario'";
$usuarios = mysqli_query($con, $sql_usuarios);

// Busca apenas os livros que estão disponíveis
$sql_livros = "SELECT id, titulo FROM livros WHERE disponivel = 1";
$livros = mysqli_query($con, $sql_livros);

// Verifica se o formulário foi enviado
if(isset($_POST['btn_cadastrar'])){

    // Recebe os dados enviados pelo formulário
    $usuario_id = $_POST['usuario_id'];
    $livro_id = $_POST['livro_id'];

    // Verifica se o livro ainda está disponível
    $sql_verifica = "
    SELECT disponivel
    FROM livros
    WHERE id = '$livro_id'
    ";

    $resultado_verifica = mysqli_query($con, $sql_verifica);

    // Armazena o resultado da consulta em um array
    $livro = mysqli_fetch_assoc($resultado_verifica);

    // Se o livro não estiver disponível, exibe mensagem de erro
    if($livro['disponivel'] != 1){

        echo "<p style='color:red'>
        Este livro não está disponível para empréstimo.
        </p>";

    }
    else{

        // Define a data atual como data de retirada
        $data_retirada = date('Y-m-d');

        // Define a data prevista para devolução (7 dias depois)
        $data_devolucao_prevista = date('Y-m-d', strtotime('+7 days'));

        // Insere o empréstimo no banco de dados
        $sql = "INSERT INTO emprestimos
        (usuario_id, livro_id, data_retirada, data_devolucao_prevista)
        VALUES
        ('$usuario_id', '$livro_id',
        '$data_retirada', '$data_devolucao_prevista')";

        // Executa o cadastro do empréstimo
        if(mysqli_query($con, $sql)){

            // Atualiza o livro para indisponível
            $sql_livro = "
            UPDATE livros
            SET disponivel = 0
            WHERE id = '$livro_id'
            ";

            mysqli_query($con, $sql_livro);

            // Mensagem de sucesso
            echo "<p style='color:green'>
            Empréstimo cadastrado com sucesso!
            </p>";
        }
        else{
            // Mensagem de erro
            echo "<p style='color:red'>
            Erro ao cadastrar empréstimo.
            </p>";
        }
    }
}
?>

<!-- Título da página -->
<h2>Cadastro de Empréstimo</h2>

<!-- Formulário de cadastro -->
<form method="POST">

Usuário:<br>

<!-- Lista de usuários -->
<select name="usuario_id" required>

<option value="">Selecione</option>

<?php
// Preenche os usuários encontrados
while($u = mysqli_fetch_assoc($usuarios)){
    echo "<option value='".$u['id']."'>";
    echo $u['nome'];
    echo "</option>";
}
?>

</select>

<br><br>

Livro:<br>

<!-- Lista de livros disponíveis -->
<select name="livro_id" required>

<option value="">Selecione</option>

<?php
// Preenche o select com os livros disponíveis
while($l = mysqli_fetch_assoc($livros)){
    echo "<option value='".$l['id']."'>";
    echo $l['titulo'];
    echo "</option>";
}
?>

</select>

<br><br>

<!-- Botão para registrar empréstimo -->
<button type="submit" name="btn_cadastrar">
    Registrar Empréstimo
</button>

<!-- Botão para limpar/recarregar a página -->
<button
type="button"
onclick="window.location.href='cadastro_emprestimo.php'">
    Cancelar
</button>

</form>

<?php

// Consulta todos os empréstimos que ainda não foram devolvidos
$sql_ativos = "
SELECT
    e.id,
    u.nome,
    l.titulo,
    e.data_retirada,
    e.data_devolucao_prevista
FROM emprestimos e
INNER JOIN usuarios u ON u.id = e.usuario_id
INNER JOIN livros l ON l.id = e.livro_id
WHERE e.data_devolucao IS NULL
";

// Executa a consulta
$emprestimos_ativos = mysqli_query($con, $sql_ativos);
?>

<br><br>

<!-- Título da tabela -->
<h2>Empréstimos Ativos</h2>

<table border="1">

<tr>
    <th>Usuário</th>
    <th>Livro</th>
    <th>Data de Retirada</th>
    <th>Data Prevista de Devolução</th>
    <th>Situação</th>
    <th>Ações</th>
</tr>

<?php

// Percorre todos os empréstimos ativos
while($e = mysqli_fetch_assoc($emprestimos_ativos)){

    echo "<tr>";

    // Exibe os dados do empréstimo
    echo "<td>".$e['nome']."</td>";
    echo "<td>".$e['titulo']."</td>";
    echo "<td>".$e['data_retirada']."</td>";
    echo "<td>".$e['data_devolucao_prevista']."</td>";

    // Verifica se o empréstimo está atrasado
    if(strtotime($e['data_devolucao_prevista']) < time()){
        echo "<td>Atrasado</td>";
    }
    else{
        echo "<td>Em andamento</td>";
    }

    // Link para registrar a devolução do livro
    echo "<td>";
    echo "<a href='devolver_livro.php?id=".$e['id']."'>
    Registrar Devolução
    </a>";
    echo "</td>";

    echo "</tr>";
}
?>

</table>