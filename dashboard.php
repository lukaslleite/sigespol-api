<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function carregarDadosCache() {
    // ✅ CORREÇÃO: Caminho relativo a partir do próprio script
    // O dashboard.php está em: /home/sigesco1/dash/api/dashboard.php
    // O cache está em: /home/sigesco1/dash/api/cache/
    $cacheDir = __DIR__ . '/../cache/';
    
    $dados = [
        'success' => false,
        'totais' => [],
        'top10Geral' => [],
        'distribuicaoMensal' => [],
        'debug' => [
            'cache_dir' => $cacheDir,
            'dir_exists' => is_dir($cacheDir)
        ]
    ];
    
    // Verificar se a pasta cache existe
    if (!is_dir($cacheDir)) {
        $dados['debug']['error'] = 'Pasta cache não encontrada';
        return $dados;
    }
    
    // ✅ CORREÇÃO: Pegar APENAS 2024 e 2025
    $arquivosOcorrencias = [];
    $anos = ['2024', '2025'];
    
    foreach ($anos as $ano) {
        $arquivo = $cacheDir . 'ocorrencias_' . $ano . '.json';
        $dados['debug']['arquivos_encontrados'][$ano] = file_exists($arquivo);
        
        if (file_exists($arquivo)) {
            $arquivosOcorrencias[] = $arquivo;
        }
    }
    
    $dados['debug']['total_arquivos'] = count($arquivosOcorrencias);
    
    // Se encontrou arquivos de ocorrências, processá-los
    if (!empty($arquivosOcorrencias)) {
        $dados = processarArquivosOcorrencias($cacheDir, $arquivosOcorrencias, $dados);
    } else {
        $dados['debug']['error'] = 'Nenhum arquivo encontrado';
    }
    
    return $dados;
}

function processarArquivosOcorrencias($cacheDir, $arquivosOcorrencias, $dados) {
    $todasOcorrencias = [];
    $codigosUnicos = [];
    $contagemTipos = [];
    
    foreach ($arquivosOcorrencias as $arquivo) {
        $conteudo = file_get_contents($arquivo);
        $dadosJson = json_decode($conteudo, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Tentar diferentes estruturas possíveis
            $ocorrencias = [];
            
            if (isset($dadosJson['dados']['dados']) && is_array($dadosJson['dados']['dados'])) {
                $ocorrencias = $dadosJson['dados']['dados'];
            } elseif (isset($dadosJson['dados']) && is_array($dadosJson['dados'])) {
                $ocorrencias = $dadosJson['dados'];
            } elseif (is_array($dadosJson)) {
                $ocorrencias = $dadosJson;
            }
            
            if (!empty($ocorrencias)) {
                $todasOcorrencias = array_merge($todasOcorrencias, $ocorrencias);
                
                foreach ($ocorrencias as $ocorrencia) {
                    // ✅ CONTAGEM GERAL: Por código único
                    if (isset($ocorrencia['codigo'])) {
                        $codigo = $ocorrencia['codigo'];
                        $codigosUnicos[$codigo] = true;
                    }
                    
                    // ✅ CONTAGEM POR TIPO: Cada tipo conta individualmente
                    if (isset($ocorrencia['tipo'])) {
                        $tipo = $ocorrencia['tipo'];
                        $contagemTipos[$tipo] = ($contagemTipos[$tipo] ?? 0) + 1;
                    }
                }
            }
        }
    }
    
    if (!empty($todasOcorrencias)) {
        $dados['success'] = true;
        
        // ✅ CONTAGEM GERAL CORRETA: Número de códigos únicos
        $dados['totais']['total'] = count($codigosUnicos);
        $dados['debug']['codigos_unicos'] = count($codigosUnicos);
        $dados['debug']['total_registros'] = count($todasOcorrencias);
        
        // Calcular totais por ano (também por códigos únicos)
        $codigosPorAno = [];
        foreach ($todasOcorrencias as $ocorrencia) {
            if (isset($ocorrencia['codigo']) && isset($ocorrencia['data'])) {
                $partesData = explode('/', $ocorrencia['data']);
                if (count($partesData) === 3) {
                    $ano = $partesData[2];
                    $codigo = $ocorrencia['codigo'];
                    
                    if (!isset($codigosPorAno[$ano])) {
                        $codigosPorAno[$ano] = [];
                    }
                    
                    $codigosPorAno[$ano][$codigo] = true;
                }
            }
        }
        
        foreach ($codigosPorAno as $ano => $codigos) {
            $dados['totais'][$ano] = count($codigos);
        }
        
        // ✅ TOP 10 CORRETO: Baseado na contagem por tipo (não por código)
        arsort($contagemTipos);
        $top10 = array_slice($contagemTipos, 0, 10);
        
        foreach ($top10 as $tipo => $quantidade) {
            $dados['top10Geral'][] = [
                'tipo' => $tipo,
                'quantidade' => $quantidade
            ];
        }
        
        // ✅ Distribuição mensal também deve usar códigos únicos
        $dados['distribuicaoMensal'] = calcularDistribuicaoMensalCorrigida($todasOcorrencias);
    }
    
    return $dados;
}

function calcularDistribuicaoMensalCorrigida($ocorrencias) {
    $distribuicao = [];
    $codigosPorMesAno = [];
    
    foreach ($ocorrencias as $ocorrencia) {
        if (isset($ocorrencia['codigo']) && isset($ocorrencia['data'])) {
            $partesData = explode('/', $ocorrencia['data']);
            if (count($partesData) === 3) {
                $ano = $partesData[2];
                $mes = $partesData[1];
                $codigo = $ocorrencia['codigo'];
                
                if (!isset($codigosPorMesAno[$ano])) {
                    $codigosPorMesAno[$ano] = [];
                }
                
                if (!isset($codigosPorMesAno[$ano][$mes])) {
                    $codigosPorMesAno[$ano][$mes] = [];
                }
                
                $codigosPorMesAno[$ano][$mes][$codigo] = true;
            }
        }
    }
    
    foreach ($codigosPorMesAno as $ano => $meses) {
        $distribuicao[$ano] = [];
        foreach ($meses as $mes => $codigos) {
            $distribuicao[$ano][$mes] = count($codigos);
        }
        
        // Garantir que todos os meses existam (mesmo com zero)
        for ($i = 1; $i <= 12; $i++) {
            $mesStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            if (!isset($distribuicao[$ano][$mesStr])) {
                $distribuicao[$ano][$mesStr] = 0;
            }
        }
        ksort($distribuicao[$ano]);
    }
    
    return $distribuicao;
}

$resultado = carregarDadosCache();
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>