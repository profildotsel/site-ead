<?php
// Desativar o cache para garantir que os dados sejam sempre os mais recentes.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF--alfa">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 800px; }
        .card-header { font-weight: bold; }
        .badge.bg-success { color: white; }
        .badge.bg-danger { color: white; }
        .log {
            background-color: #333;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <h1 class="text-center mb-4">Relatório de Diagnóstico do Sistema</h1>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                Verificação do Servidor (PHP)
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Versão do PHP
                    <?php
                    $php_version_ok = version_compare(phpversion(), '7.4', '>=');
                    echo $php_version_ok
                        ? '<span class="badge bg-success">'.phpversion().' (OK)</span>'
                        : '<span class="badge bg-danger">'.phpversion().' (Requer 7.4+)</span>';
                    ?>
                </li>
                <?php
                $extensions = ['pdo_mysql', 'json', 'mbstring', 'gd'];
                foreach ($extensions as $ext) {
                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                    echo "Extensão PHP: <strong>{$ext}</strong>";
                    echo extension_loaded($ext)
                        ? '<span class="badge bg-success">Instalada</span>'
                        : '<span class="badge bg-danger">Não Instalada</span>';
                    echo '</li>';
                }
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    Exibição de Erros (display_errors)
                    <?php
                    $display_errors = ini_get('display_errors');
                    echo $display_errors == 1 || strtolower($display_errors) === 'on'
                        ? '<span class="badge bg-success">Ativado</span>'
                        : '<span class="badge bg-warning text-dark">Desativado</span>';
                    ?>
                </li>
            </ul>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                Conexão com Banco de Dados
            </div>
            <div class="card-body">
                <?php
                // Tenta carregar a configuração do banco de dados
                if (file_exists('config/database.php')) {
                    require_once 'config/database.php';
                    $database = new Database();
                    try {
                        $db = $database->getConnection();
                        echo '<div class="alert alert-success">Conexão com o banco de dados bem-sucedida!</div>';
                    } catch (PDOException $exception) {
                        echo '<div class="alert alert-danger"><strong>Falha na conexão:</strong> ' . $exception->getMessage() . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">Arquivo <strong>config/database.php</strong> não encontrado. Não foi possível testar a conexão.</div>';
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-secondary text-white">
                Diagnóstico do Frontend (Lado do Cliente)
            </div>
            <div class="card-body">
                <p>Abra o console do navegador (pressione <strong>F12</strong> e clique em "Console") para ver os logs de diagnóstico do JavaScript.</p>
                <div class="log" id="console-log">Aguardando logs do console...</div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logElement = document.getElementById('console-log');
            let logContent = '';

            function log(message, type = 'info') {
                const colorMap = {
                    'success': 'lightgreen',
                    'error': 'salmon',
                    'warning': 'gold',
                    'info': 'lightblue'
                };
                const formattedMessage = `[${new Date().toLocaleTimeString()}] [${type.toUpperCase()}] ${message}\n`;
                console.log(formattedMessage);
                logContent += `<span style="color:${colorMap[type]}">${formattedMessage}</span>`;
                logElement.innerHTML = logContent;
            }

            log('Iniciando diagnóstico do Frontend...');

            // 1. Verificar jQuery
            if (window.jQuery) {
                log('jQuery ' + $.fn.jquery + ' carregado com sucesso.', 'success');
            } else {
                log('jQuery não foi encontrado!', 'error');
            }

            // 2. Verificar Bootstrap
            if (typeof bootstrap !== 'undefined') {
                log('Bootstrap 5 JS carregado com sucesso.', 'success');
            } else {
                log('Bootstrap 5 JS não foi encontrado!', 'error');
            }

            // 3. Verificar CKEditor
            if (typeof CKEDITOR !== 'undefined') {
                log('CKEditor carregado com sucesso.', 'success');
            } else {
                log('CKEditor não foi encontrado!', 'error');
            }
            
            // 4. Testar AJAX para carregar aulas (simulação)
            log('Tentando simular requisição AJAX para carregar aulas...', 'info');
            // Substitua 'api/get_lessons.php?course_id=1' pelo seu endpoint real se tiver um.
            // Por enquanto, vamos testar o próprio 'manage_courses.php'
            $.ajax({
                url: 'manage_courses.php', // Testa se o arquivo principal responde sem erros
                method: 'GET',
                success: function() {
                    log('A requisição AJAX para "manage_courses.php" foi bem-sucedida (código 200). Isso é um bom sinal.', 'success');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    log(`Falha na requisição AJAX: ${textStatus} - ${errorThrown}`, 'error');
                    log(`Status da resposta: ${jqXHR.status}`, 'warning');
                    log('Verifique o console do navegador para mais detalhes sobre o erro na rede.', 'warning');
                }
            });
        });
    </script>
</body>
</html>