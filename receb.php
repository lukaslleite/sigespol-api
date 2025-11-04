<?php
// receb.php — recebe arquivos JSON via POST e salva no servidor

header('Content-Type: text/plain; charset=utf-8');

// Pasta onde os arquivos serão salvos
$destino = __DIR__ . '/cache';

// Cria a pasta se não existir
if (!is_dir($destino)) {
    mkdir($destino, 0777, true);
}

if (!isset($_FILES['arquivo'])) {
    http_response_code(400);
    exit("❌ Nenhum arquivo recebido.");
}

$nomeArquivo = basename($_FILES['arquivo']['name']);
$caminhoDestino = $destino . '/' . $nomeArquivo;

// Move o arquivo temporário para o destino
if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoDestino)) {
    echo "✅ Arquivo recebido com sucesso: $nomeArquivo";
} else {
    http_response_code(500);
    echo "❌ Erro ao mover o arquivo.";
}
