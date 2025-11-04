<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function carregarTiposCache() {
    $cacheDir = __DIR__ . '/../../cache/';
    $tipos = [];
    
    // Tentar carregar do cache de tipos específico
    if (file_exists($cacheDir . 'tipos_ocorrencias.json')) {
        $tiposCache = json_decode(file_get_contents($cacheDir . 'tipos_ocorrencias.json'), true);
        if (is_array($tiposCache)) {
            $tipos = $tiposCache;
        }
    }
    
    // Se não encontrou, tentar extrair do top10
    if (empty($tipos) && file_exists($cacheDir . 'top10_geral.json')) {
        $top10 = json_decode(file_get_contents($cacheDir . 'top10_geral.json'), true);
        foreach ($top10 as $item) {
            if (isset($item['tipo']) && !in_array($item['tipo'], $tipos)) {
                $tipos[] = $item['tipo'];
            }
        }
    }
    
    // Se ainda não encontrou, tentar extrair de anos específicos
    if (empty($tipos)) {
        $anos = ['2024', '2025'];
        foreach ($anos as $ano) {
            $arquivoAno = $cacheDir . $ano . '.json';
            if (file_exists($arquivoAno)) {
                $dadosAno = json_decode(file_get_contents($arquivoAno), true);
                if (is_array($dadosAno)) {
                    foreach ($dadosAno as $ocorrencia) {
                        if (isset($ocorrencia['tipo']) && !in_array($ocorrencia['tipo'], $tipos)) {
                            $tipos[] = $ocorrencia['tipo'];
                        }
                    }
                }
            }
        }
    }
    
    // Fallback para tipos padrão
    if (empty($tipos)) {
        $tipos = [
			'HOMICÍDIO',
			'ABANDONO DE INCAPAZ',
			'CRIME DE TRÂNSITO',
			'ACIDENTE AEROVIÁRIO',
			'ACIDENTE COM ARMA DE FOGO',
			'ACIDENTE COM EMBARCAÇÕES',
			'ACIDENTE DE VEICULO SEM VÍTIMA',
			'ACIDENTE COM VEÍCULO OFICIAL',
			'ACIDENTE DE TRABALHO',
			'ACIDENTE DE VEÍCULO COM VÍTIMA (ATROPELO/COLISÃO)',
			'ACIDENTE DE VEÍCULO COM VÍTIMA PRESA EM FERRAGENS',
			'ACIDENTE FERROVIÁRIO',
			'AFOGAMENTO',
			'AGRESSÃO/LESOES CORPORAIS',
			'ALAGAMENTO',
			'ALARME BANCÁRIO',
			'AMEAÇA',
			'APREENSÃO DE ARMA BRANCA',
			'APREENSÃO DE ARMA DE FOGO',
			'ATENDIMENTO A GESTANTE',
			'ATENTADO A BOMBA',
			'ATENTADO VIOLENTO AO PUDOR',
			'ATO OBSCENO',
			'ATO PÚBLICO (PASSEATAS/PROTESTO)',
			'AVERIGUAÇÃO',
			'BUSCA DE PESSOA',
			'CAPTURA DE FUGITIVO',
			'CAPTURA E RESGATE DE ANIMAIS',
			'CAPTURA E/OU EXTERMÍNIO DE INSETOS',
			'CONDUÇÃO DE CUSTODIADO',
			'CRIME AMBIENTAL',
			'DANOS MATERIAIS',
			'DESABAMENTO',
			'DIREÇÃO PERIGOSA',
			'DISPARO DE ARMA DE FOGO',
			'DISPUTA DE CORRIDA EM VIA PÚBLICA',
			'DOENTE MENTAL',
			'DROGAS/PORTE',
			'DROGAS/TRÁFICO',
			'DROGAS/USO',
			'EMBRIAGUEZ',
			'EMERGÊNCIA COM MATERIAL RADIOATIVO',
			'EMERGÊNCIA COM PRODUTOS PERIGOSOS',
			'EMERGÊNCIA EM ELEVADORES',
			'ENCHENTE',
			'ENCONTRO DE CADÁVER - APARENTE MORTE NATURAL',
			'ESTELIONATO/FRAUDE',
			'ESTUPRO',
			'EXPLOSÃO',
			'EXPLOSIVOS EM LOCAIS DIVERSOS',
			'EXTORSÃO',
			'EXTORSÃO MEDIANTE SEQUESTRO',
			'FUGA DE PRESO',
			'FURTO A BANCO',
			'FURTO A CAIXA ELETRÔNICO',
			'FURTO A CASA LOTÉRICA',
			'FURTO A ESCOLA PARTICULAR',
			'FURTO A ESCOLA PÚBLICA',
			'FURTO A ESTABELECIMENTO COMERCIAL',
			'FURTO A IGREJA',
			'FURTO A INDÚSTRIA',
			'FURTO A VEÍCULO',
			'FURTO SIMPLES',
			'FURTO/CESTA DO POVO',
			'INCÊNDIO E VAZAMENTO DE GASES E LÍQUIDOS INFLAMÁVEIS',
			'INCÊNDIO EM AUTOMÓVEL',
			'INCÊNDIO EM BARRACA OU QUIOSQUE',
			'INCÊNDIO EM CONSULTÓRIO OU CLÍNICA',
			'INCÊNDIO EM EDIFÍCIOS ACIMA DE 4 PAVIMENTOS',
			'INCÊNDIO EM EQUIPAMENTOS ENERGIZADOS',
			'INCÊNDIO EM ESCOLAS',
			'INCÊNDIO EM ESCRITÓRIO',
			'INCÊNDIO EM ESTABELECIMENTO COMERCIAL OU DEPÓSITOS',
			'INCÊNDIO EM GALERIA',
			'INCÊNDIO EM GLP',
			'INCÊNDIO EM HABITAÇÕES COLETIVAS',
			'INCÊNDIO EM HOSPITAIS',
			'INCÊNDIO EM INDÚSTRIA',
			'INCÊNDIO EM LIXO',
			'INCÊNDIO EM LOCAIS DE REUNIÃO PÚBLICA',
			'INCÊNDIO EM POSTO DE GASOLINA',
			'INCÊNDIO EM QUARTÉIS',
			'INCÊNDIO EM REPARTIÇÕES PÚBLICAS',
			'INCÊNDIO EM RESIDÊNCIA',
			'INCÊNDIO EM VEGETAÇÃO',
			'INCÊNDIO FLORESTAL',
			'JOGO DE AZAR',
			'LINCHAMENTO',
			'MAUS TRATOS',
			'OMISSÃO DE SOCORRO',
			'APOIO A INSTITUIÇÕES (MINISTÉRIO PÚBLICO, ORGÃO GOVERNAMENTAL, ETC)',
			'OUTRAS OCORRÊNCIAS NÃO RELACIONADAS',
			'PERTURBAÇÃO DO SOSSEGO PÚBLICO',
			'PESCA PREDATÓRIA (EXPLOSIVO)',
			'PORTE  ILEGAL DE ARMA',
			'PRESTAÇÃO DE SOCORRO',
			'REBELIÃO DE PRESOS',
			'RECEPTAÇÃO',
			'ROUBO',
			'ROUBO A AGÊNCIA DOS CORREIOS',
			'ROUBO A BANCO',
			'ROUBO A BANCO/LATROCÍNIO',
			'ROUBO A CARGA',
			'ROUBO A CARRO FORTE',
			'ROUBO A CASA LOTÉRICA',
			'ROUBO A ESTABELECIMENTO COMERCIAL',
			'ROUBO A INDÚSTRIA',
			'ROUBO A ÔNIBUS',
			'ROUBO A TRANSPORTE ALTERNATIVO',
			'ROUBO A VEÍCULO DE CARGA',
			'SALVAMENTO DE PESSOA EM RISCO',
			'SALVAMENTO EM ALTURA',
			'SALVAMENTO EM ESPAÇO CONFINADO',
			'SEQUESTRO E CÁRCERE PRIVADO',
			'SOTERRAMENTO',
			'TENTATIVA DE FURTO',
			'TENTATIVA DE HOMICÍDIO',
			'TENTATIVA DE LATROCÍNIO',
			'TENTATIVA DE ROUBO',
			'TENTATIVA DE ROUBO A BANCO',
			'TENTATIVA DE SEQUESTRO',
			'TENTATIVA DE SUÍCIDIO',
			'RECUPERAÇÃO DE VEÍCULO ROUBADO/FURTADO',
			'VIOLAÇÃO DE DOMICÍLIO',
			'VÍTIMA DE TRAUMA',
			'SUICÍDIO',
			'PRISÃO DE PESSOA COM MANDADO EM ABERTO',
			'APREENSÃO DE VEÍCULOS (INFR TRÂNSITO)',
			'DESACATO',
			'MARIA DA PENHA',
			'AUTO DE RESISTÊNCIA',
			'APREENSÃO DE SIMULACRO DE ARMA DE FOGO',
			'ADULTERAÇÃO DE SINAL IDENTIFICADOR DE VEÍCULO',
			'VIAS DE FATO',
			'INCÊNDIO (CRIME) ART. 250 CP',
			'LATROCÍNIO',
			'IMPORTUNAÇÃO SEXUAL',
			'ACIDENTE DOMÉSTICO COM MORTE',
			'RECUPERAÇÃO DE VEÍCULO COM RESTRIÇÃO RENAJUD (BSUCA E APREENSÃO)',
			'CRIME ELEITORAL',
			'DANO AO PATRIMÔNIO PUBLICO',
			'APREENSÃO DE DROGAS SEM CONDUÇÃO DE PESSOAS',
			'ROUBO DE VEÍCULO'
		];
    }
    
    // Ordenar tipos alfabeticamente
    sort($tipos);
    
    return [
        'success' => true,
        'tipos' => array_values(array_unique($tipos))
    ];
}

$resultado = carregarTiposCache();
echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
?>