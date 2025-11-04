<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function carregarDadosCache() {
    $cacheDir = __DIR__ . '/../cache/';
    $dados = [
        'success' => false,
        'totais' => [],
        'top10Geral' => [],
        'distribuicaoMensal' => []
    ];
    
    // Carregar totais
    if (file_exists($cacheDir . 'totais.json')) {
        $dados['totais'] = json_decode(file_get_contents($cacheDir . 'totais.json'), true);
    }
    
    // Carregar top10
    if (file_exists($cacheDir . 'top10_geral.json')) {
        $dados['top10Geral'] = json_decode(file_get_contents($cacheDir . 'top10_geral.json'), true);
    }
    
    // Carregar distribuição mensal
    if (file_exists($cacheDir . 'distribuicao_mensal.json')) {
        $dados['distribuicaoMensal'] = json_decode(file_get_contents($cacheDir . 'distribuicao_mensal.json'), true);
    }
    
    $dados['success'] = !empty($dados['totais']);
    return $dados;
}

$resultado = carregarDadosCache();
echo json_encode($resultado);
?>