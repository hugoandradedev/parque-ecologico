<?php
/**
 * Script para popular a tabela bloqueios com datas comemorativas do Brasil
 * Execute via terminal: php populate_bloqueios.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Bloqueio.php';

try {
    $conn = Database::connect();
    $bloqueio = new Bloqueio($conn);
    
    $ano = date('Y');
    echo "Carregando datas comemorativas de $ano...\n";
    
    // Datas fixas
    $datas = [
        "$ano-01-01" => 'Ano Novo',
        "$ano-04-21" => 'Tiradentes',
        "$ano-05-01" => 'Dia do Trabalho',
        "$ano-09-07" => 'Independência do Brasil',
        "$ano-10-12" => 'Nossa Senhora Aparecida',
        "$ano-11-02" => 'Finados',
        "$ano-11-15" => 'Proclamação da República',
        "$ano-11-20" => 'Consciência Negra',
        "$ano-12-25" => 'Natal'
    ];
    
    // Datas móveis
    $easter = easter_date($ano);
    $sexta = strtotime('-2 days', $easter);
    $corpus = strtotime('+60 days', $easter);
    
    $datas[date('Y-m-d', $sexta)] = 'Sexta-feira Santa';
    $datas[date('Y-m-d', $corpus)] = 'Corpus Christi';
    
    $adicionadas = 0;
    $puladas = 0;
    
    foreach ($datas as $data => $motivo) {
        if ($bloqueio->existeDataBloqueada($data)) {
            echo "⊘ Data $data ($motivo) já existe\n";
            $puladas++;
            continue;
        }
        
        if ($bloqueio->create($data, $motivo)) {
            echo "✓ Data $data ($motivo) adicionada\n";
            $adicionadas++;
        } else {
            echo "✗ Erro ao adicionar $data ($motivo)\n";
        }
    }
    
    echo "\n=== Resultado ===\n";
    echo "Adicionadas: $adicionadas\n";
    echo "Puladas (já existem): $puladas\n";
    echo "Total de datas: " . count($datas) . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
