<?php
// Inclui arquivo que gera a conexão
include "../conexao.php";

// Inclui o cabeçalho padrão do usuário (layout/menu)
include "head_usuario.php";

// Inicia ou recupera a sessão atual
session_start();

// Variáveis de controle
$num_linhas = 0;
$exesql_retirados  = '';

// Verifica se o usuário está logado
if(isset($_SESSION['status']) and $_SESSION['status']!= "" ){

    // Usuário logado (email vindo da sessão)
    $user = $_SESSION['status'];

    // Conexão com o banco de dados pela função
    $con = cria_conexao();

    // Consulta histórico de multas do usuário
    $sql_retirados = "SELECT 
                l.titulo,
                l.autor,
                l.classificacao,
                e.data_retirada,
                e.data_devolucao,
                e.data_devolucao_prevista,
                m.valor as valor_multa,
                m.motivo as motivo_multa,
                cast(m.paga as SIGNED) as multa_paga
            from emprestimos e
            inner join usuarios u on e.usuario_id = u.id
            inner join livros l on e.livro_id = l.id
            inner join multas m on m.emprestimo_id = e.id
            where u.email='$user'
            order by m.paga asc, e.data_retirada desc, e.data_devolucao_prevista desc";

    // Executa consulta
    $exesql_retirados = mysqli_query($con, $sql_retirados);

    // Fecha conexão
    mysqli_close($con);

    // Conta quantos registros foram encontrados
    $num_linhas = mysqli_num_rows($exesql_retirados);

}
else{

    // Redireciona para login caso usuário não esteja autenticado
    header("location: ../login.php");
}

?>

<html lang="pt-br">
    <head>
        <meta charset="utf-8">
    </head>

    <body>

        <!-- Botão para gerar relatório PDF das multas -->
        <p></p>
            <form action="gerar_pdf_multas.php" method="post">
                <button type='submit'>Gerar relatório de multas</button>
            </form>
        </p>

        <br>

        <!-- Título da página -->
        <h2>Histórico de Multas Registradas</h2>

        <div class="lista">
            <?php 

            // Se existirem registros
            if ($num_linhas >= 1) {

                // Percorre todas as multas do usuário
                while ($resul = mysqli_fetch_assoc($exesql_retirados)) {

                    echo "<hr>";

                    // Informações do livro
                    echo "<strong>Título: </strong>" . $resul['titulo']. " <br>";
                    echo "<strong>Autor(a): </strong>" . $resul['autor']. " <br>";
                    echo "<strong>Classificação: </strong>" . $resul['classificacao']. " <br>";

                    // Informações do empréstimo
                    echo "<strong>Data da Retirada: </strong>" . $resul['data_retirada']. " <br>";
                    echo "<strong>Data da Devolução Prevista: </strong>" . $resul['data_devolucao_prevista']. " <br>";

                    // Exibe data de devolução caso exista
                    if( $resul['data_devolucao'] != null){
                        echo "<strong>Data da Devolução: </strong>" . $resul['data_devolucao']. " <br>";
                    }

                    echo "<hr class='tracejado'>";

                    // Status da multa
                    if($resul['multa_paga'] == 1) {
                        echo "<strong> Multa quitada :) </strong> <br>";
                    }
                    else{
                        echo "<strong> Multa em haver :( </strong> <br> Venha pagar para não aumentar ;) <br>";
                    }

                    // Detalhes da multa
                    echo "<strong>Valor multa: </strong> R$" . $resul['valor_multa']. " <br>";
                    echo "<strong>Motivo da Multa: </strong>" . $resul['motivo_multa']. " <br><br> ";
                }

            } else {

                // Mensagem caso não existam multas registradas
                echo "<h3>[|]  :)> Retire um livro!! :)>  [|]  </h3>";
            }

            ?>
        </div>

    </body>
</html>