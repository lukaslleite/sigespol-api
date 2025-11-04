<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function statusCache() {
    $cacheDir = __DIR__ . '/../../../cache/';
    $status = [
        'success' => false,
        'cache' => [
            'ultimaAtualizacao' => null,
            'anosComCache' => [],
            'totalRegistros' => 0,
            'tamanhoCache' => 0
        ]
    ];
    
    if (is_dir($cacheDir)) {
        $arquivos = glob($cacheDir . '*.json');
        $status['success'] = true;
        $status['cache']['ultimaAtualizacao'] = date('c');
        
        foreach ($arquivos as $arquivo) {
            $status['cache']['tamanhoCache'] += filesize($arquivo);
            if (preg_match('/\d{4}\.json/', $arquivo, $matches)) {
                $status['cache']['anosComCache'][] = $matches[0];
            }
        }
        
        if (file_exists($cacheDir . 'totais.json')) {
            $totais = json_decode(file_get_contents($cacheDir . 'totais.json'), true);
            $status['cache']['totalRegistros'] = $totais['total'] ?? 0;
        }
    }
    
    return $status;
}

echo json_encode(statusCache());
?>