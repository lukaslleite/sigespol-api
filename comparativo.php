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

function gerarDistribuicaoPorTipo($ocorrencias, $tipoSelecionado, $periodo = 'mensal', $dedupePorCodigoTipo = true) {
    $distribuicao = ($periodo === 'anual') ? 0 : array_fill(1, 12, 0);
    $visto = [];

    foreach ($ocorrencias as $oc) {
        if (empty($oc['data']) || empty($oc['codigo'])) continue;
        $tipoBusca = trim($tipoSelecionado);
        if ($tipoBusca === '') continue;

        $campoTipos = isset($oc['tipoOriginal']) && strlen(trim($oc['tipoOriginal'])) > 0 ? $oc['tipoOriginal'] : (isset($oc['tipo']) ? $oc['tipo'] : '');
        if ($campoTipos === '') continue;

        $partes = array_map('trim', explode(',', $campoTipos));
        $encontrou = false;
        $tipoEncontrado = '';

		// Lista de tokens que invalidam uma correspondência (se presentes na mesma parte)
		$exclusoes = [
		'TENTATIVA',
		'TENTADO',
		'TENTATIVA DE',
		'LESÃO', // por precaução, caso apareça em frases compostas
		// adicione outros termos que queira excluir
		];
		
        foreach ($partes as $p) {
			if ($p === '') continue;


			// corresponde ao termo buscado (case-insensitive)
			if (stripos($p, $tipoBusca) === false) continue;


			// se qualquer token de exclusão aparecer nesta parte, ignorar
			$temExclusao = false;
			foreach ($exclusoes as $exc) {
			if (stripos($p, $exc) !== false) {
			$temExclusao = true;
			break;
			}
			}
			if ($temExclusao) continue; // pula "TENTATIVA DE HOMICÍDIO", etc.


			// aqui temos um tipo válido correspondente a $tipoBusca (ex: "HOMICÍDIO")
			$encontrou = true;
			$tipoEncontrado = $p;
			break;
			}

        if (!$encontrou) continue;

        if ($dedupePorCodigoTipo) {
            $chave = $oc['codigo'] . '|' . $tipoEncontrado;
            if (isset($visto[$chave])) continue;
            $visto[$chave] = true;
        }

        if ($periodo === 'anual') {
            $distribuicao += 1;
        } else {
            $dt = explode('/', $oc['data']);
            if (count($dt) !== 3) continue;
            $mes = intval($dt[1]);
            if ($mes >= 1 && $mes <= 12) $distribuicao[$mes] += 1;
        }
    }
    return $distribuicao;
}

$ocorrenciasAnterior = carregarOcorrencias($anoAnterior, $cacheDir);
$ocorrenciasAtual = carregarOcorrencias($anoAtual, $cacheDir);

$distribuicaoAnterior = gerarDistribuicaoPorTipo($ocorrenciasAnterior, $tipoSelecionado, $periodo, true);
$distribuicaoAtual = gerarDistribuicaoPorTipo($ocorrenciasAtual, $tipoSelecionado, $periodo, true);

$totalAnterior = $periodo === 'anual' ? $distribuicaoAnterior : array_sum($distribuicaoAnterior);
$totalAtual = $periodo === 'anual' ? $distribuicaoAtual : array_sum($distribuicaoAtual);

$diferenca = $totalAtual - $totalAnterior;
$variacaoPercentual = $totalAnterior > 0 ? (($totalAtual - $totalAnterior) / $totalAnterior) * 100 : 0;

$resposta = [
    'success' => true,
    'estatisticas' => [
        'totalPeriodo1' => $totalAnterior,
        'totalPeriodo2' => $totalAtual,
        'diferenca' => $diferenca,
        'variacaoPercentual' => $variacaoPercentual
    ],
    'dados' => [
        'distribuicaoComparativa' => [
            $anoAnterior => $periodo === 'anual' ? [$distribuicaoAnterior] : $distribuicaoAnterior,
            $anoAtual => $periodo === 'anual' ? [$distribuicaoAtual] : $distribuicaoAtual
        ]
    ],
    'anoAnterior' => $anoAnterior,
    'anoAtual' => $anoAtual,
    'tipoSelecionado' => $tipoSelecionado,
    'periodo' => $periodo
];

echo json_encode($resposta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
