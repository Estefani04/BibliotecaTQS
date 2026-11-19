<?php
// Inclui arquivo que gera a conexão
include "conexao.php";

/**
 * Calcula multas de usuários com base em atrasos
 */
function calcula_multa($usuario = null){
    $sql = "";

    // Conexão com banco de dados pela função
    $con = cria_conexao();

    if($usuario != null){

        /**
         * Consulta:
         * - Calcula dias de atraso
         * - Multiplica por 0.75 para gerar valor da multa
         * - Verifica se já existe multa para o empréstimo
         */
        $sql = 
        "SELECT e.id as id_emprestimo, 
        DATEDIFF( date(now()), date(e.data_devolucao_prevista)) * 0.75 as valor_multa,
        case when e.id = m.emprestimo_id then 'S' else 'N' end as flg_multa_existente
        FROM emprestimos e
        inner join usuarios u on u.id = e.usuario_id
        left join multas m on m.emprestimo_id = e.id
        where e.data_devolucao is null
            and u.email = '$usuario'
            and date(e.data_devolucao_prevista) < date(now())
            and (date(m.ultima_atualizacao) < date(now()) or m.ultima_atualizacao is null)";
    }
    else{
         /**
         * Consulta:
         * - Calcula dias de atraso
         * - Multiplica por 0.75 para gerar valor da multa
         * - Verifica se já existe multa para o empréstimo
         */
        $sql = 
        "SELECT e.id as id_emprestimo, 
        DATEDIFF( date(now()), date(e.data_devolucao_prevista)) * 0.75 as valor_multa,
        case when e.id = m.emprestimo_id then 'S' else 'N' end as flg_multa_existente
        FROM emprestimos e
        left join multas m on m.emprestimo_id = e.id
        where e.data_devolucao is null
            and date(e.data_devolucao_prevista) < date(now())
            and (date(m.ultima_atualizacao) < date(now()) or m.ultima_atualizacao is null)";
    }

    // Executa consulta
    $exesql = mysqli_query($con, $sql);

    // Conta resultados
    $num_linhas = mysqli_num_rows($exesql);

    // Fecha conexão
    mysqli_close($con);

    // Retorna resultados se houver multas
    if ($num_linhas >= 1){
        return $exesql;
    }
    else return null;
}

/**
 * Atualiza ou insere multas no banco de dados
 */
function atualiza_multa($multas){

    // Só executa se houver dados
    if($multas != null){

        // Conexão com banco pela função
        $con = cria_conexao();

        // Percorre todas as multas calculadas
        while ($resul = mysqli_fetch_assoc($multas)){

            $valor_multa = $resul['valor_multa'];
            $id_emprestimo = $resul['id_emprestimo'];

            // Se já existe multa, atualiza
            if($resul['flg_multa_existente'] == 'S'){

                $sql_multa = "UPDATE multas 
                set valor = '$valor_multa',
                ultima_atualizacao = now()
                where emprestimo_id = '$id_emprestimo'";
            }

            // Caso contrário, insere nova multa
            else{
                $sql_multa = "INSERT INTO multas (valor, motivo, paga, emprestimo_id, ultima_atualizacao)
                VALUES ('$valor_multa', 'atraso', '0', ' $id_emprestimo', now())";
            }
            
            // Executa query de update/insert
            mysqli_query($con, $sql_multa);
        }

        // Fecha conexão
        mysqli_close($con);
    }
}
?>