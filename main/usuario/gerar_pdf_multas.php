<?php
// Inclui arquivo que gera a conexão
include "../conexao.php";

// Inicia ou recupera a sessão do usuário
session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['status'])) {

    // Se não estiver logado, redireciona para login
    header("Location: ../login.php");
    exit;
}

// Importa a biblioteca FPDF para geração de PDF
require('../biblioteca/fpdf186/fpdf.php');

// Usuário logado (email)
$user = $_SESSION['status'];

// Conexão com banco de dados pela função
$con = cria_conexao();

// Consulta de multas do usuário logado
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
$resultado_sql = mysqli_query($con, $sql_retirados);

// Conta registros retornados
$num_linhas = mysqli_num_rows($resultado_sql);

// Se houver multas, gera o PDF
if ($num_linhas > 0) {

    // Cria PDF em modo paisagem (L = landscape)
    $pdf = new FPDF('L', 'mm', 'A4');

    // Adiciona página
    $pdf->AddPage();

    // Data atual para nome do relatório
    $data = date('d-m-Y');

    // Título do documento
    $titulo = "Relatório de Multas $data";

    // Define fonte do título
    $pdf->SetFont('Arial', 'B', 14);

    // Imprime título centralizado
    $pdf->Cell(0, 10, utf8_decode($titulo), 0, 1, 'C');

    $pdf->Ln(3);

    // Cabeçalho da tabela
    $pdf->SetFont('Arial', 'B', 8);

    $pdf->Cell(25, 8, utf8_decode('Título'), 1);
    $pdf->Cell(35, 8, 'Autor', 1);
    $pdf->Cell(50, 8, utf8_decode('Class.'), 1);
    $pdf->Cell(25, 8, 'Retirada', 1);
    $pdf->Cell(25, 8, 'Dev. Prev.', 1);
    $pdf->Cell(25, 8, utf8_decode('Devolução'), 1);
    $pdf->Cell(30, 8, 'Status', 1);
    $pdf->Cell(20, 8, 'Valor', 1);
    $pdf->Cell(25, 8, 'Motivo', 1);

    $pdf->Ln();

    // Fonte para os dados
    $pdf->SetFont('Arial', '', 7);

    // Percorre resultados da consulta
    while ($resul = mysqli_fetch_assoc($resultado_sql)) {

        // Define status da multa
        $status = ($resul['multa_paga'] == 1)
            ? 'Multa Paga'
            : 'Multa em haver';

        // Define devolução (caso NULL)
        $devolucao = ($resul['data_devolucao'] == null)
            ? utf8_decode('Não devolvido')
            : $resul['data_devolucao'];

        // Linha da tabela no PDF
        $pdf->Cell(25, 8, utf8_decode($resul['titulo']), 1);
        $pdf->Cell(35, 8, utf8_decode($resul['autor']), 1);
        $pdf->Cell(50, 8, utf8_decode($resul['classificacao']), 1);
        $pdf->Cell(25, 8, $resul['data_retirada'], 1);
        $pdf->Cell(25, 8, $resul['data_devolucao_prevista'], 1);
        $pdf->Cell(25, 8, $devolucao, 1);
        $pdf->Cell(30, 8, $status, 1);
        $pdf->Cell(20, 8, 'R$ ' . $resul['valor_multa'], 1);
        $pdf->Cell(25, 8, utf8_decode($resul['motivo_multa']), 1);

        $pdf->Ln();
    }

    // Nome do arquivo gerado
    $nome_arquivo = "multas_$data.pdf";

    // Força download do PDF no navegador
    $pdf->Output('D', $nome_arquivo);
    exit;
}

// Fecha conexão caso não haja registros
mysqli_close($con);

// Mensagem caso não existam multas
echo "Nenhuma multa encontrada.";
?>