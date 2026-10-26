<?php
// Inclui arquivo que gera a conexão
include "../conexao.php";

// Inclui o cabeçalho padrão das páginas do usuário
include "head_usuario.php";

// Inicia ou recupera a sessão atual
session_start();

// Inicializa variáveis de controle
$num_linhas = 0;
$exesql_retirados  = '';

// Verifica se existe um usuário logado
if(isset($_SESSION['status']) and $_SESSION['status']!= "" ){

    // Obtém o e-mail do usuário armazenado na sessão
    $user = $_SESSION['status'];

    // Realiza a conexão com o banco de dados pela função
    $con = cria_conexao();

    // Consulta os empréstimos ativos do usuário
    $sql_retirados = "SELECT 
                l.titulo,
                l.autor,
                l.classificacao,
                e.data_retirada,
                e.data_devolucao_prevista,
                m.valor as valor_multa,
                m.motivo as motivo_multa,
                m.paga as multa_paga
            from emprestimos e
            inner join usuarios u on e.usuario_id = u.id
            inner join livros l on e.livro_id = l.id
            left join multas m on m.emprestimo_id = e.id
            where u.email='$user' and e.data_devolucao is null
            order by e.data_retirada desc";

    // Executa a consulta
    $exesql_retirados = mysqli_query($con, $sql_retirados);

    // Fecha a conexão com o banco de dados
    mysqli_close($con);

    // Obtém a quantidade de registros encontrados
    $num_linhas = mysqli_num_rows($exesql_retirados);

}
else{

   // Caso não exista usuário logado, redireciona para a página de login
   header("location: ../login.php");
}
?>

<html lang="pt-br">
<head>
	<meta charset="utf-8">
</head>
<body>

    <br>

    <!-- Título principal da página -->
    <h2>Bem-Vindo ao Menu Principal da Bibioteca</h2>

    <form method='POST'>
        <div>

           <?php

           // Verifica se o usuário possui empréstimos ativos
           if ($num_linhas >= 1) {

                echo "<h3>Livros com você no momento:</h3>";

                // Percorre todos os empréstimos encontrados
                while ($resul = mysqli_fetch_assoc($exesql_retirados)) {

                    echo "<hr>";

                    // Exibe informações do livro emprestado
                    echo "<strong>Título: </strong>" . $resul['titulo']. " <br>";
                    echo "<strong>Autor(a): </strong>" . $resul['autor']. " <br>";
                    echo "<strong>Classificação: </strong>" . $resul['classificacao']. " <br>";

                    // Exibe informações do empréstimo
                    echo "<strong>Data da Retirada: </strong>" . $resul['data_retirada']. " <br>";
                    echo "<strong>Data da Devolução Prevista: </strong>" . $resul['data_devolucao_prevista']. " <br>";

                    // Verifica se existe multa associada ao empréstimo
                    if( $resul['valor_multa'] == null){

                        // Exibe mensagem caso não exista multa
                        echo "<strong>Multa: </strong> Sem multa. :) <br><br>";

                    }
                    else{

                        // Exibe informações da multa
                        echo "<hr class='tracejado'>";

                        // Verifica se a multa já foi paga
                        if($resul['multa_paga'] == 1) {
                            echo "<strong> Multa quitada :) </strong> <br>";
                        }
                        else{
                            echo "<strong> Multa em haver :( </strong> <br> Venha pagar para não aumentar ;) <br>";
                        }

                        // Exibe valor e motivo da multa
                        echo "<strong>Valor multa: </strong> R$" . $resul['valor_multa']. " <br>";
                        echo "<strong>Motivo da Multa: </strong>" . $resul['motivo_multa']. "  <br><br> ";
                    }
                }

            }
            else {

                // Mensagem exibida quando o usuário não possui empréstimos ativos
                echo "<h3>[|]  :)> Retire um livro!! :)>  [|]  </h3>";
            }

            ?>

        </div>
	</form>

</body>
</html>