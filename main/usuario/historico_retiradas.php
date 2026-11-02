<?php
// Inclui o cabeçalho padrão do usuário (menu, layout, etc.)
include "head_usuario.php";

// Importa funções auxiliares (pesquisa, ordenação, mensagens e a conexão)
include "funcoes.php";

// Inicia ou recupera a sessão
session_start();

// Variáveis de controle
$num_linhas = 0;
$exesql_retirados  = '';
$mensagem = "";

// Array que armazenará todo o histórico de empréstimos
$todo_historico = [];
// Array usado para exibição (pode ser filtrado/ordenado)
$mostra = [];

// Verifica se o usuário está logado
if(isset($_SESSION['status']) and $_SESSION['status']!= "" ){

    // Recupera o usuário logado
    $user = $_SESSION['status'];

    // Conexão com banco de dados pela função
    $con = cria_conexao();

    // Consulta histórico completo de empréstimos do usuário
    $sql_retirados = "SELECT 
                l.titulo,
                l.autor,
                l.classificacao,
                e.data_retirada,
                e.data_devolucao,
                e.data_devolucao_prevista,
                m.valor as valor_multa,
                m.motivo as motivo_multa,
                m.paga as multa_paga
            from emprestimos e
            inner join usuarios u on e.usuario_id = u.id
            inner join livros l on e.livro_id = l.id
            left join multas m on m.emprestimo_id = e.id
            where u.email='$user'
            order by e.data_retirada desc, e.data_devolucao_prevista desc";

    // Executa consulta
    $exesql_retirados = mysqli_query($con, $sql_retirados);

    // Fecha conexão
    mysqli_close($con);

    // Conta registros retornados
    $num_linhas = mysqli_num_rows($exesql_retirados);

    // Se não houver histórico
    if($num_linhas < 1){
       $mensagem = "<h3>[|]  :)> Retire um livro!! :)>  [|]  </h3>";
    }
    else{

        // Converte resultado do banco em array estruturado
        while ($resul = mysqli_fetch_assoc($exesql_retirados)){

            $todo_historico[] = [
                "titulo" => $resul['titulo'], 
                "autor"=> $resul['autor'], 
                "classificacao"=> $resul['classificacao'], 
                "data_retirada" => $resul['data_retirada'], 
                "data_devolucao_prevista"=> $resul['data_devolucao_prevista'], 
                "valor_multa" => $resul['valor_multa'],
                "motivo_multa" => $resul['motivo_multa'],
                "multa_paga" => $resul['multa_paga'], 
                "data_devolucao"=> $resul['data_devolucao']
            ];
        }

        // Inicialmente, o que será exibido é o histórico completo
        $mostra = $todo_historico;
    }

}
else{

    // Redireciona para login caso não esteja autenticado
    header("location: ../login.php");
}

/**
 * PROCESSAMENTO DE PESQUISA NO HISTÓRICO
 */
if(isset($_POST['btn_pesquisa'])){

    // Valor digitado pelo usuário
    $valorPesquisa = $_POST['valorPesquisa'];

    // Campo escolhido para pesquisa
    $objetoPesquisa = $_POST['objetoPesquisa'];

    // Se selecionar "todos", limpa filtro
    if($objetoPesquisa == 'todos'){
        $mostra = $todo_historico;
        $mensagem = "";
    }

    // Se houver texto de pesquisa
    elseif($valorPesquisa != ""){

        // Aplica filtro de pesquisa
        $mostra = pesquisa($todo_historico, $objetoPesquisa, $valorPesquisa);

        // Atualiza quantidade de resultados
        $num_linhas = count($mostra);

        // Gera mensagem da pesquisa
        $mensagem = mensagem_pesquisa($objetoPesquisa, $valorPesquisa, $num_linhas);
    }
    else{

        // Caso usuário não digite nada
        $mensagem = "<p> <strong> Ops.. Parece que você esqueceu de preencher o texto da pesquisa. </strong> Adicione um texto para melhores resultados. :) </p>";
    }
}

/**
 * ORDENAÇÃO CRESCENTE DO HISTÓRICO
 */
if(isset($_POST["btn_ordena_crescente"])){

    $objetoOrdenacao = $_POST["objetoOrdenacao"];

    // Base de dados original
    if (isset($_POST["valorPesquisa"]) && $_POST["valorPesquisa"] != "" && $_POST["objetoPesquisa"] != "todos") {
        $mostra = pesquisa($todo_historico, $_POST["objetoPesquisa"], $_POST["valorPesquisa"]);
    }

    // Ordena crescente
    $mostra = ordenaCrescente($mostra, $objetoOrdenacao);
}

/**
 * ORDENAÇÃO DECRESCENTE DO HISTÓRICO
 */
if(isset($_POST["btn_ordena_decrescente"])){

    $objetoOrdenacao = $_POST["objetoOrdenacao"];

    // Mantém filtro de pesquisa se existir
    if (isset($_POST["valorPesquisa"]) && $_POST["valorPesquisa"] != "" && $_POST["objetoPesquisa"] != "todos") {
        $mostra = pesquisa($todo_historico, $_POST["objetoPesquisa"], $_POST["valorPesquisa"]);
    }

    // Ordena decrescente
    $mostra = ordenaDecrescente($mostra, $objetoOrdenacao);
}

?>

<html lang="pt-br">
    <head>
        <meta charset="utf-8">
    </head>

    <body>

        <!-- TÍTULO DA PÁGINA -->
        <br>
        <h2>Bem-Vindo ao Histórico de Retiradas</h2>

        <!-- FORMULÁRIO DE PESQUISA -->
        <form method='POST'>
            <strong> Pesquisar no histórico de retiradas:</strong>

            <p><input type='text' name='valorPesquisa'></p>

            <p>
                <!-- Filtros de pesquisa -->
                <input type="radio" id="titulo" name="objetoPesquisa" value="titulo">
                <label for="titulo"> Titulo</label>

                <input type="radio" id="autor" name="objetoPesquisa" value="autor">
                <label for="autor">Autor</label>

                <input type="radio" id="classificacao" name="objetoPesquisa" value="classificacao">
                <label for="classificacao">Classificacao</label>

                <input type="radio" id="todos" name="objetoPesquisa" value="todos" required>
                <label for="todos">Limpar Pesquisa</label>

                <button type='submit' name='btn_pesquisa'> Buscar </button>
            </p>

        </form>


        <!-- FORMULÁRIO DE ORDENAÇÃO -->
        <form method='POST'>

            <strong> Ordenar Livros do histórico:</strong> <br><br>

            <!-- Campos de ordenação -->
            <input type="radio" id="titulo" name="objetoOrdenacao" value="titulo">
            <label for="tituloOrd"> Titulo</label>

            <input type="radio" id="autor" name="objetoOrdenacao" value="autor">
            <label for="autorORD">Autor</label>

            <input type="radio" id="classificacao" name="objetoOrdenacao" value="classificacao">
            <label for="classificacaoOrd">Classificacao</label>

            <input type="radio" id="data_retiradaOrd" name="objetoOrdenacao" value="data_retirada">
            <label for="data_retiradaOrd">Data de retirada</label>

            <input type="radio" id="data_devolucao_prevista" name="objetoOrdenacao" value="data_devolucao_prevista">
            <label for="data_devolucao_previstaOrd">Data de devolução prevista</label>

            <input type="radio" id="data_devolucao" name="objetoOrdenacao" value="data_devolucao" required>
            <label for="data_devolucaoOrd">Data de devolução</label>

            <input type="radio" id="valor_multa" name="objetoOrdenacao" value="valor_multa" required>
            <label for="valor_multaOrd">Multa</label>

            <!-- Mantém filtros ao ordenar -->
            <input type="hidden" name="valorPesquisa" value="<?= $_POST['valorPesquisa'] ?? '' ?>">
            <input type="hidden" name="objetoPesquisa" value="<?= $_POST['objetoPesquisa'] ?? 'todos' ?>"><br>

            <button type='submit' name='btn_ordena_crescente'> Ordena Crescente </button>
            <button type='submit' name='btn_ordena_decrescente'> Ordena Decrescente </button>

        </form>


        <!-- EXIBIÇÃO DO HISTÓRICO -->
        <div class="lista">

            <?php

            // Exibe mensagem (se houver)
            echo "$mensagem";

            // Se existir histórico
            if ($num_linhas >= 1) {

                echo "<h3>Livros que você já retirou:</h3>";

                // Percorre histórico filtrado/ordenado
                foreach($mostra as $resul) {

                    echo "<hr>";

                    echo "<strong>Título: </strong>" . $resul['titulo']. " <br>";
                    echo "<strong>Autor(a): </strong>" . $resul['autor']. " <br>";
                    echo "<strong>Classificação: </strong>" . $resul['classificacao']. " <br>";

                    echo "<strong>Data da Retirada: </strong>" . $resul['data_retirada']. " <br>";
                    echo "<strong>Data da Devolução Prevista: </strong>" . $resul['data_devolucao_prevista']. " <br>";

                    // Mostra devolução se existir
                    if( $resul['data_devolucao'] != null){
                        echo "<strong>Data da Devolução: </strong>" . $resul['data_devolucao']. " <br>";
                    }

                    // Verifica multa
                    if( $resul['valor_multa'] == null){
                        echo "<strong>Multa: </strong> Sem multa. :) <br><br>";
                    }
                    else{

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
                        echo "<strong>Motivo da Multa: </strong>" . $resul['motivo_multa']. "  <br><br> ";
                    }
                }
            }

            ?>

        </div>

    </body>
</html>