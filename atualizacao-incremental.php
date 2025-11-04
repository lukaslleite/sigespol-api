<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function atualizacaoIncremental() {
    $cacheDir = __DIR__ . '/../../cache/';
    $resultado = [
        'success' => false,
        'mensagem' => '',
        'dadosAtualizados' => []
    ];

    echo "🔄 Iniciando atualização incremental...\n";

    // Listar arquivos de cache
    $arquivos = glob($cacheDir . 'ocorrencias_*.json');
    $arquivos = array_filter($arquivos, function($arquivo) {
        return preg_match('/ocorrencias_\d{4}\.json$/', basename($arquivo));
    });

    if (empty($arquivos)) {
        $resultado['mensagem'] = '❌ Nenhum arquivo de cache encontrado.';
        return $resultado;
    }

    // Encontrar arquivo mais recente com dados
    $arquivoValido = null;
    $lista = [];
    $anoValido = null;

    // Ordenar anos decrescentemente
    $anos = [];
    foreach ($arquivos as $arquivo) {
        if (preg_match('/ocorrencias_(\d{4})\.json/', $arquivo, $matches)) {
            $anos[] = (int)$matches[1];
        }
    }
    rsort($anos);

    foreach ($anos as $ano) {
        $arquivo = $cacheDir . "ocorrencias_{$ano}.json";
        if (!file_exists($arquivo)) continue;

        $conteudo = json_decode(file_get_contents($arquivo), true);
        $dados = $conteudo['dados']['dados'] ?? $conteudo['dados'] ?? [];

        if (!empty($dados)) {
            $arquivoValido = $arquivo;
            $lista = $dados;
            $anoValido = $ano;
            break;
        }
    }

    if (!$arquivoValido) {
        $resultado['mensagem'] = '⚠️ Nenhum arquivo válido com dados encontrados.';
        return $resultado;
    }

    // Encontrar última ocorrência
    $ultima = null;
    $ultimoCodigo = 0;
    foreach ($lista as $ocorrencia) {
        $codigo = intval(str_replace('#', '', $ocorrencia['codigo'] ?? '0'));
        if ($codigo > $ultimoCodigo) {
            $ultimoCodigo = $codigo;
            $ultima = $ocorrencia;
        }
    }

    if (!$ultima) {
        $resultado['mensagem'] = '❌ Não foi possível encontrar a última ocorrência.';
        return $resultado;
    }

    $dataUltimaOcorrencia = $ultima['data'] ?? '';
    echo "📘 Último registro detectado: #{$ultimoCodigo} ({$dataUltimaOcorrencia})\n";

    // 🔍 Buscar novas ocorrências (simulação - você precisará implementar o scraper em PHP)
    $novasOcorrencias = simularScrapeNovasOcorrencias($ultimoCodigo, $dataUltimaOcorrencia);
    echo "📊 Novas ocorrências encontradas no site: " . count($novasOcorrencias) . "\n";

    if (empty($novasOcorrencias)) {
        $resultado['mensagem'] = 'ℹ️ Nenhuma nova ocorrência encontrada.';
        $resultado['success'] = true;
        return $resultado;
    }

    // 🔎 Filtrar duplicadas
    $codigosExistentes = [];
    foreach ($lista as $ocorrencia) {
        $codigo = intval(str_replace('#', '', $ocorrencia['codigo'] ?? '0'));
        $codigosExistentes[$codigo] = true;
    }

    $filtradas = array_filter($novasOcorrencias, function($ocorrencia) use ($codigosExistentes) {
        $codigo = intval(str_replace('#', '', $ocorrencia['codigo'] ?? '0'));
        return !isset($codigosExistentes[$codigo]);
    });

    $filtradas = array_values($filtradas); // Reindexar array
    echo "✅ Novas ocorrências após filtro de duplicadas: " . count($filtradas) . "\n";

    if (empty($filtradas)) {
        $resultado['mensagem'] = 'ℹ️ Nenhuma nova ocorrência realmente nova após o filtro.';
        $resultado['success'] = true;
        return $resultado;
    }

    // 🧮 Atualizar arquivo de cache
    $novasOrdenadas = array_merge($lista, $filtradas);
    usort($novasOrdenadas, function($a, $b) {
        $ca = intval(str_replace('#', '', $a['codigo'] ?? '0'));
        $cb = intval(str_replace('#', '', $b['codigo'] ?? '0'));
        return $ca - $cb;
    });

    $novoConteudo = [
        'dados' => ['dados' => $novasOrdenadas],
        'total' => count($novasOrdenadas),
        'atualizado' => date('c')
    ];

    file_put_contents($arquivoValido, json_encode($novoConteudo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "💾 Cache atualizado ({$anoValido}): agora com {$novoConteudo['total']} ocorrências.\n";

    // 📝 Exibir resumo
    $novoUltimo = end($filtradas);
    echo "🆕 Última ocorrência adicionada: {$novoUltimo['codigo']} ({$novoUltimo['data']})\n";

    $resultado['success'] = true;
    $resultado['mensagem'] = 'Atualização incremental concluída com sucesso';
    $resultado['dadosAtualizados'] = [
        'novosRegistros' => count($filtradas),
        'totalAgora' => count($novasOrdenadas),
        'arquivo' => basename($arquivoValido),
        'ultimaOcorrencia' => $novoUltimo['codigo'] ?? '',
        'timestamp' => date('Y-m-d H:i:s')
    ];

    return $resultado;
}

/**
 * Simula o scraping de novas ocorrências
 * Você precisará implementar o scraper real em PHP aqui
 */
function simularScrapeNovasOcorrencias($ultimoCodigo, $dataUltima) {
    // Simulação - retorna array vazio ou algumas ocorrências mockadas
    $novas = [];
    
    // 30% de chance de retornar novas ocorrências (para teste)
    if (rand(1, 100) <= 30) {
        $quantidade = rand(1, 5);
        for ($i = 1; $i <= $quantidade; $i++) {
            $novoCodigo = $ultimoCodigo + $i;
            $novas[] = [
                'codigo' => '#' . $novoCodigo,
                'data' => date('d/m/Y'),
                'hora' => sprintf('%02d:%02d', rand(0, 23), rand(0, 59)),
                'tipo' => 'OCORRÊNCIA SIMULADA',
                'municipio' => 'Município Teste',
                'descricao' => 'Ocorrência gerada automaticamente para teste'
            ];
        }
    }
    
    return $novas;
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Método não permitido. Use POST.'
    ]);
    exit;
}

// Executar atualização incremental
$resultado = atualizacaoIncremental();
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>