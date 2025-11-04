<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Diretório do cache
$cacheDir = __DIR__ . '/../cache';

// Parâmetros do front-end
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$mes = isset($_GET['mes']) && ctype_digit($_GET['mes']) && (int)$_GET['mes'] >= 1 && (int)$_GET['mes'] <= 12
    ? str_pad((int)$_GET['mes'], 2, '0', STR_PAD_LEFT)
    : '';

// Função para carregar ocorrências do JSON
function carregarOcorrencias($ano, $cacheDir) {
    $arquivo = "$cacheDir/ocorrencias_{$ano}.json";
    if (!file_exists($arquivo)) {
        return [];
    }

    $conteudo = file_get_contents($arquivo);
    $json = json_decode($conteudo, true);

    if (isset($json['dados']['dados']) && is_array($json['dados']['dados'])) {
        return $json['dados']['dados'];
    }

    return [];
}

// Função para filtrar e agrupar ocorrências
function filtrarOcorrencias($ocorrencias, $tipo, $mes) {
    $filtradas = [];
    $vistos = [];

    foreach ($ocorrencias as $oc) {
        if (!isset($oc['tipo']) || !isset($oc['data'])) continue;

        $tipoOriginal = strtoupper(trim($oc['tipoOriginal'] ?? $oc['tipo']));
        $codigo = trim($oc['codigo'] ?? '');
        if ($codigo === '') continue;

        $mesOcorrencia = substr($oc['data'], 3, 2);

        // ✅ Filtro por tipo: correspondência exata (sem variações)
        if ($tipo && strtoupper($oc['tipo']) !== strtoupper($tipo)) continue;

        // ✅ Filtro por mês
        if ($mes && $mesOcorrencia !== $mes) continue;

        // ✅ Evitar duplicados tipo+codigo
        $chaveUnica = $tipoOriginal . '|' . $codigo;
        if (isset($vistos[$chaveUnica])) continue;
        $vistos[$chaveUnica] = true;

        $filtradas[] = [
            'codigo' => $codigo,
            'data' => $oc['data'] ?? '',
            'hora' => $oc['hora'] ?? '',
            'municipio' => $oc['municipio'] ?? '',
            'tipo' => $tipoOriginal
        ];
    }

    return $filtradas;
}

// Função para calcular comparativo com o mês anterior (somente se mês selecionado)
function calcularComparativoMesAnterior($ocorrencias, $ano, $mes, $tipo, $cacheDir) {
    if (empty($mes)) {
        return null; // 🚫 Não calcula se for "Todos os meses"
    }

    // Converter para número
    $mesAtualNum = (int)$mes;
    if ($mesAtualNum < 1 || $mesAtualNum > 12) {
        return null; // 🚫 Mês inválido
    }

    // Calcular mês e ano anterior
    $mesAnterior = $mesAtualNum - 1;
    $anoAnterior = $ano;
    if ($mesAnterior < 1) {
        $mesAnterior = 12;
        $anoAnterior -= 1;
    }

    // Garantir que arquivo do ano anterior exista
    $arquivoAnterior = "$cacheDir/ocorrencias_{$anoAnterior}.json";
    if (!file_exists($arquivoAnterior)) {
        return null; // 🚫 Sem arquivo do ano anterior → sem comparativo
    }

    // Filtrar mês atual e anterior
    $dadosMesAtual = filtrarOcorrencias($ocorrencias, $tipo, str_pad($mesAtualNum, 2, '0', STR_PAD_LEFT));
    $ocorrenciasMesAnterior = carregarOcorrencias($anoAnterior, $cacheDir);
    $dadosMesAnterior = filtrarOcorrencias($ocorrenciasMesAnterior, $tipo, str_pad($mesAnterior, 2, '0', STR_PAD_LEFT));

    // Calcular totais e variação
    $totalAtual = is_array($dadosMesAtual) ? count($dadosMesAtual) : 0;
    $totalAnterior = is_array($dadosMesAnterior) ? count($dadosMesAnterior) : 0;

    if ($totalAnterior <= 0) {
        $variacao = 0;
    } else {
        $variacao = (($totalAtual - $totalAnterior) / $totalAnterior) * 100;
    }

    return [
        'mesAnterior' => $totalAnterior,
        'variacao' => round($variacao, 1),
        'periodoAnterior' => sprintf('%02d/%d', $mesAnterior, $anoAnterior)
    ];
}


// Carregar e filtrar ocorrências
$ocorrencias = carregarOcorrencias($ano, $cacheDir);
$filtradas = filtrarOcorrencias($ocorrencias, $tipo, $mes);

// Cálculo do comparativo (somente se mês informado)
$comparativo = !empty($mes)
    ? calcularComparativoMesAnterior($ocorrencias, $ano, $mes, $tipo, $cacheDir)
    : null;

// Montagem da resposta final
$resposta = [
    'success' => true,
    'consulta' => [
        'tipo' => $tipo ?: 'Todos',
        'ano' => $ano,
        'periodo' => $mes ? sprintf('%02d/%d', $mes, $ano) : "Ano {$ano}",
        'total' => count($filtradas),
        'detalhes' => array_slice(array_reverse($filtradas), 0, 10)
    ]
];

// Adiciona comparativo apenas se existir
if (!empty($comparativo)) {
    $resposta['consulta']['comparativoMesAnterior'] = $comparativo;
}

echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
