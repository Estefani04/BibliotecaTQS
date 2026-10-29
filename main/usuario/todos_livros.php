<?php

// Inclui o cabeçalho da área do usuário (layout, menu, etc.)
include "head_usuario.php";

// Importa funções auxiliares (pesquisa, ordenação, mensagens etc.)
include "funcoes.php";

// Inicia ou recupera a sessão atual
session_start();

// Variáveis de controle e armazenamento
$num_linhas = 0;
$exesql_resultado  = '';
$mensagem = "";

// Array principal com todos os livros do acervo
$todo_acervo = [];
// Array que será exibido na tela (pode ser filtrado/ordenado)
$mostra = [];

/**
 * Função responsável por buscar todo o acervo de livros no banco de dados
 */
function todoAcervo(){

    // SQL para buscar todos os livros disponiveis ordenados por título
    $sql_todo_acervo = "SELECT 
        l.titulo,
        l.autor,
        l.classificacao,
        l.editora,
        l.edicao,
        l.ano_publicacao as ano,
        l.numero_paginas as paginas,
        l.assunto,
        l.disponivel
    from livros l 
    where l.disponivel != 2
    order by l.titulo
    ";

    // Conexão com o banco de dados pela função
    $con = cria_conexao();
    
    // Executa a consulta SQL
    $exesql_resultado = mysqli_query($con, $sql_todo_acervo);
    
    // Fecha conexão
    mysqli_close($con);

    // Retorna o resultado da consulta
    return $exesql_resultado;
}

// Verifica se o usuário está logado
if(isset($_SESSION['status']) and $_SESSION['status']!= "" ){

    // Usuário logado (email armazenado na sessão)
    $user = $_SESSION['status'];

    // Busca todo o acervo
    $exesql_todo_acervo = todoAcervo();

    // Conta quantos livros existem
    $num_linhas = mysqli_num_rows($exesql_todo_acervo);

    // Se não houver livros no acervo
    if($num_linhas < 1){
       $mensagem = "<h3>[|]  Estamos nos abastecendo para você ;) [|] <3 </h3> <br> Em breve novidades";
    }
    else{

        // Percorre todos os registros do banco e transforma em array
        while ($resul = mysqli_fetch_assoc($exesql_todo_acervo)){

            $todo_acervo[] = [
                "titulo" => $resul['titulo'],
                "autor"=> $resul['autor'], 
                "classificacao"=> $resul['classificacao'],
                "editora" => $resul['editora'], 
                "edicao"=> $resul['edicao'],
                "ano" => $resul['ano'],
                "paginas"=> $resul['paginas'],
                "assunto" => $resul['assunto'],
                "disponivel"=> $resul['disponivel']
            ];
        }

        // Inicialmente, o que será mostrado é o acervo completo
        $mostra = $todo_acervo;
    }

}
else{

    // Se não estiver logado, redireciona para login
    header("location: ../login.php");
}

/**
 * PROCESSAMENTO DE PESQUISA
 */
if(isset($_POST["btn_pesquisa"])){

    // Campo escolhido para pesquisa (titulo, autor, etc.)
    $objetoPesquisa = $_POST["objetoPesquisa"];

    // Texto da pesquisa
    $valorPesquisa = $_POST["valorPesquisa"];

    // Se escolher "todos", limpa filtro
    if($objetoPesquisa == 'todos'){
        $mostra = $todo_acervo;
        $mensagem = "";
    }

    // Se houver valor de pesquisa
    elseif($valorPesquisa != ""){

        // Função de pesquisa (vem de funcoes.php)
        $mostra = pesquisa($todo_acervo,$objetoPesquisa, $valorPesquisa);

        // Atualiza quantidade de resultados
        $num_linhas = count($mostra);

        // Gera mensagem de retorno da pesquisa
        $mensagem = mensagem_pesquisa($objetoPesquisa, $valorPesquisa, $num_linhas);
    }
    else{

        // Caso o usuário não digite nada
        $mensagem = "<p> <strong> Ops.. Parece que você esqueceu de preencher o texto da pesquisa. </strong> Adicione um texto para melhores resultados. :) </p>";
    }
}

/**
 * ORDENAÇÃO CRESCENTE
 */
if(isset($_POST["btn_ordena_crescente"])){

    $objetoOrdenacao = $_POST["objetoOrdenacao"];

    // Parte sempre do acervo completo
    $mostra = $todo_acervo;

    // Mantém filtro de pesquisa se existir
    if (isset($_POST["valorPesquisa"]) && $_POST["valorPesquisa"] != "" && $_POST["objetoPesquisa"] != "todos") {
        $mostra = pesquisa($mostra, $_POST["objetoPesquisa"], $_POST["valorPesquisa"]);
    }

    // Ordena crescente
    $mostra = ordenaCrescente($mostra, $objetoOrdenacao);
}

/**
 * ORDENAÇÃO DECRESCENTE
 */
if(isset($_POST["btn_ordena_decrescente"])){

    $objetoOrdenacao = $_POST["objetoOrdenacao"];

    $mostra = $todo_acervo;

    // Mantém filtro de pesquisa se existir
    if (isset($_POST["valorPesquisa"]) && $_POST["valorPesquisa"] != "" && $_POST["objetoPesquisa"] != "todos") {
        $mostra = pesquisa($mostra, $_POST["objetoPesquisa"], $_POST["valorPesquisa"]);
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

    <!-- FORMULÁRIO DE PESQUISA -->
    <form method='POST'>
        <br>
        <strong> Pesquisar em Nosso Acervo:</strong>

        <p><input type='text' name='valorPesquisa'></p>

        <p>
            <!-- Opções de filtro de pesquisa -->
            <input type="radio" id="titulo" name="objetoPesquisa" value="titulo">
            <label for="titulo"> Titulo</label>

            <input type="radio" id="autor" name="objetoPesquisa" value="autor">
            <label for="autor">Autor</label>

            <input type="radio" id="ano" name="objetoPesquisa" value="ano">
            <label for="ano">Ano</label>

            <input type="radio" id="classificacao" name="objetoPesquisa" value="classificacao">
            <label for="classificacao">Classificacao</label>

            <input type="radio" id="editora" name="objetoPesquisa" value="editora">
            <label for="editora">Editora</label>

            <input type="radio" id="todos" name="objetoPesquisa" value="todos" required>
            <label for="todos">Limpar Pesquisa</label>

            <button type='submit' name='btn_pesquisa'> Buscar </button>
        </p>

    </form>


    <!-- FORMULÁRIO DE ORDENAÇÃO -->
    <form method='POST'>

        <strong> Ordenar Livros em Nosso Acervo:</strong> <br><br>

        <!-- Campos de ordenação -->
        <input type="radio" id="titulo" name="objetoOrdenacao" value="titulo">
        <label for="tituloOrd"> Titulo</label>

        <input type="radio" id="autor" name="objetoOrdenacao" value="autor">
        <label for="autorORD">Autor</label>

        <input type="radio" id="ano" name="objetoOrdenacao" value="ano">
        <label for="anoOrd">Ano</label>

        <input type="radio" id="classificacao" name="objetoOrdenacao" value="classificacao">
        <label for="classificacaoOrd">Classificacao</label>

        <input type="radio" id="editoraOrd" name="objetoOrdenacao" value="editora">
        <label for="editoraOrd">Editora</label>

        <input type="radio" id="edicao" name="objetoOrdenacao" value="edicao">
        <label for="edicaoOrd">Edicao</label>

        <input type="radio" id="paginas" name="objetoOrdenacao" value="paginas" required>
        <label for="paginasOrd">N° páginas</label>

        <!-- Mantém valores da pesquisa ao ordenar -->
        <input type="hidden" name="valorPesquisa" value="<?= $_POST['valorPesquisa'] ?? '' ?>">
        <input type="hidden" name="objetoPesquisa" value="<?= $_POST['objetoPesquisa'] ?? 'todos' ?>"><br>

        <button type='submit' name='btn_ordena_crescente'> Ordena Crescente </button>
        <button type='submit' name='btn_ordena_decrescente'> Ordena Decrescente </button>

    </form>


    <h2>Nosso acervo de livros:</h2>

    <div class="lista">

        <?php

        // Exibe mensagem de status (ex: sem livros, ou mensagens de pesquisa)
        echo $mensagem;

        // Se houver livros no acervo
        if ($num_linhas >= 1) {

            // Percorre lista exibível (pode estar filtrada/ordenada)
            foreach($mostra as $resul) {

                echo "<hr>";

                echo "<strong>Titulo: </strong>" . $resul['titulo']. " <br>";
                echo "<strong>Autor: </strong>" . $resul['autor']. " <br>";
                echo "<strong>Classificacao: </strong>" . $resul['classificacao']. " <br>";
                echo "<strong>Editora: </strong>" . $resul['editora']. " <br>";
                echo "<strong>Edição: </strong>" . $resul['edicao']. " <br>";
                echo "<strong>Ano Públicação: </strong>" . $resul['ano']. " <br>";

                echo "<strong>N° de páginas: </strong>" . $resul['paginas']. " <br>";
                echo "<strong>Assunto: </strong>" . $resul['assunto']. " <br>";

                // Verifica disponibilidade do livro
                if( $resul['disponivel'] == 0){
                    echo "<strong> Não está disponível no momento :( </strong> <br><br>";
                }
                else {
                    echo "<strong> Esperando por um leitor :) </strong> <br><br>";
                }
            }
        }

        ?>

    </div>

    </body>
</html>