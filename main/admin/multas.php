<?php

// Exibe o menu do administrador
include "head_admin.php";

// Importa o arquivo responsável pela conexão com o banco de dados
require_once("../conexao.php");

// Cria a conexão com o banco de dados
$con = cria_conexao();

// Consulta SQL para listar as multas cadastradas, busca o ID da multa, nome do usuário,
// valor da multa, motivo e status de pagamento
$sql = "
SELECT
    m.id,
    u.nome,
    m.valor,
    m.motivo,
    m.paga
FROM multas m
INNER JOIN emprestimos e ON e.id = m.emprestimo_id
INNER JOIN usuarios u ON u.id = e.usuario_id
";

// Executa a consulta SQL
$resultado = mysqli_query($con, $sql);
?>

<!-- Título da página -->
<h2>Controle de Multas</h2>

<!-- Tabela para exibir as multas cadastradas -->
<table border="1">

<tr>

    <!-- Cabeçalho da tabela -->
    <th>Usuário</th>
    <th>Valor</th>
    <th>Motivo</th>
    <th>Status</th>
    <th>Ações</th>

</tr>

<?php

// Percorre todas as multas retornadas pela consulta
while($m = mysqli_fetch_assoc($resultado)){

    echo "<tr>";

    // Exibe o nome do usuário
    echo "<td>".$m['nome']."</td>";

    // Exibe o valor da multa
    echo "<td>R$ ".$m['valor']."</td>";

    // Exibe o motivo da multa
    echo "<td>".$m['motivo']."</td>";

    // Exibe o status da multa
    echo "<td>".($m['paga'] == 1 ? 'Paga' : 'Pendente')."</td>";

    // Exibe as ações disponíveis para a multa
    echo "<td>";

    // Se a multa estiver pendente, exibe o botão para marcar como paga
    if($m['paga'] == 0){

        echo "<a href='pagar_multa.php?id=".$m['id']."'>
        Marcar como Paga
        </a>";

    }
    else{

        // Se a multa já estiver paga,
        // exibe o botão para marcar como pendente
        echo "<a href='pagar_multa.php?id=".$m['id']."'>
        Marcar como Pendente
        </a>";
    }

    echo "</td>";

    echo "</tr>";
}
?>

</table>