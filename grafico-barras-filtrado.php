<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$cacheDir = __DIR__ . '/../cache';
$tipoSelecionado = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$periodo = isset($_GET['periodo']) ? trim($_GET['periodo']) : 'mensal';

$anoAtual = date('Y');
$anoAnterior = $anoAtual - 1;

function carregarOcorrencias($ano, $cacheDir) {
    $arquivo = "{$cacheDir}/ocorrencias_{$ano}.json";
    if (!file_exists($arquivo)) return [];

    $conteudo = file_get_contents($arquivo);
    $json = json_decode($conteudo, true);

    if (isset($json['dados']['dados']) && is_array($json['dados']['dados'])) {
        return $json['dados']['dados'];
    }
    return [];
}

function gerarDistribuicaoPorTipo($ocorrencias, $tipoSelecionado, $dedupePorCodigoTipo = true) {
    $distribuicao = array_fill(1, 12, 0);
    $visto = [];

    foreach ($ocorrencias as $oc) {
        if (empty($oc['data']) || empty($oc['codigo'])) continue;

        $campoTipos = isset($oc['tipoOriginal']) && strlen(trim($oc['tipoOriginal'])) > 0 ? $oc['tipoOriginal'] : (isset($oc['tipo']) ? $oc['tipo'] : '');
        if ($campoTipos === '') continue;

        $partes = array_map('trim', explode(',', $campoTipos));

        foreach ($partes as $tipo) {
            if ($tipoSelecionado !== '' && stripos($tipo, $tipoSelecionado) === false) continue;

            if ($dedupePorCodigoTipo) {
                $chave = $oc['codigo'] . '|' . $tipo;
                if (isset($visto[$chave])) continue;
                $visto[$chave] = true;
            }

            $dt = explode('/', $oc['data']);
            if (count($dt) !== 3) continue;
            $mes = intval($dt[1]);
            if ($mes >= 1 && $mes <= 12) {
                $distribuicao[$mes] += 1;
            }
        }
    }

    ksort($distribuicao);
    return $distribuicao;
}

$ocorrenciasAnterior = carregarOcorrencias($anoAnterior, $cacheDir);
$ocorrenciasAtual = carregarOcorrencias($anoAtual, $cacheDir);

$dados = [
    'distribuicaoMensal' => [
        $anoAnterior => gerarDistribuicaoPorTipo($ocorrenciasAnterior, $tipoSelecionado, true),
        $anoAtual => gerarDistribuicaoPorTipo($ocorrenciasAtual, $tipoSelecionado, true)
    ]
];

echo json_encode([
    'success' => true,
    'dados' => $dados,
    'anoAnterior' => (int)$anoAnterior,
    'anoAtual' => (int)$anoAtual,
    'tipoSelecionado' => $tipoSelecionado ?: 'Todos'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
