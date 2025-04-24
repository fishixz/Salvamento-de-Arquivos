<?php
header('Content-Type: application/json');

// Configurações
$uploadDir = __DIR__ . '/arquivos';
$senhaZip = '20071982maria';

// Cria a pasta se não existir
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Envio via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['arquivo'])) {
        $arquivo = $_FILES['arquivo'];
        $nomeOriginal = basename($arquivo['name']);
        $tmp = $arquivo['tmp_name'];
        $destino = $uploadDir . '/' . $nomeOriginal;

        // Move o arquivo para a pasta /arquivos
        if (move_uploaded_file($tmp, $destino)) {
            $zipName = pathinfo($nomeOriginal, PATHINFO_FILENAME) . '.zip';
            $zipPath = $uploadDir . '/' . $zipName;

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
                // Define a senha
                $zip->setPassword($senhaZip);

                // Adiciona o arquivo e aplica criptografia AES-256
                $zip->addFile($destino, $nomeOriginal);
                $zip->setEncryptionName($nomeOriginal, ZipArchive::EM_AES_256);

                $zip->close();

                // Remove o arquivo original
                unlink($destino);

                echo json_encode([
                    "mensagem" => "Feito! Você mandou bem.🚀",
                    "arquivo" => $zipName
                ]);
            } else {
                echo json_encode(["erro" => "Eita! Deu ruim na hora de zipar o arquivo."]);
            }
        } else {
            echo json_encode(["erro" => "O arquivo travou na mudança. Tenta de novo aí."]);
        }
    } else {
        echo json_encode(["erro" => "Ué... cadê o arquivo?"]);
    }
    exit;
}

// Caso não seja POST
http_response_code(405);
echo json_encode(["erro" => "Método não permitido. Use POST."]);