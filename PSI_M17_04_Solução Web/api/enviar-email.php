<?php
/**
 * API de Envio de Emails para DermaCare
 * Envia notificações de marcações por email
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuração do servidor SMTP
define('SMTP_HOST', 'smtp.gmail.com'); // ou seu servidor SMTP
define('SMTP_PORT', 587);
define('SMTP_USER', 'seuemail@gmail.com'); // Configurar com seu email
define('SMTP_PASS', 'suasenha'); // Configurar com sua senha ou App Password
define('FROM_EMAIL', 'dermacare@clinica.pt');
define('FROM_NAME', 'DermaCare - Clínica Dermatológica');

// Processar apenas requisições POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

// Obter dados do JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

// Validar dados obrigatórios
$tipo = $data['tipo'] ?? '';
$emailDestino = $data['email'] ?? '';

if (empty($tipo) || empty($emailDestino)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo e email são obrigatórios']);
    exit;
}

// Processar baseado no tipo
switch ($tipo) {
    case 'confirmacao_marcacao':
        $resultado = enviarConfirmacaoMarcacao($data);
        break;
    
    case 'cancelamento':
        $resultado = enviarCancelamento($data);
        break;
    
    case 'lembrete':
        $resultado = enviarLembrete($data);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de email inválido']);
        exit;
}

// Retornar resultado
if ($resultado['sucesso']) {
    echo json_encode([
        'success' => true,
        'message' => 'Email enviado com sucesso'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $resultado['erro']
    ]);
}

/**
 * Envia email de confirmação de marcação
 */
function enviarConfirmacaoMarcacao($data) {
    $nome = $data['nome'] ?? 'Cliente';
    $email = $data['email'];
    $servico = $data['servico'] ?? 'Consulta';
    $medico = $data['medico'] ?? 'Médico';
    $data_consulta = $data['data'] ?? '';
    $hora = $data['hora'] ?? '';
    $observacoes = $data['observacoes'] ?? '';
    
    $assunto = '✅ Consulta Agendada - DermaCare';
    
    $mensagem = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { background-color: white; padding: 30px; border-radius: 5px; margin-top: 20px; }
            .info-box { background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            .button { display: inline-block; padding: 12px 30px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            h1 { margin: 0; }
            strong { color: #007bff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🏥 DermaCare</h1>
                <p>Clínica Dermatológica</p>
            </div>
            
            <div class='content'>
                <h2>Olá, {$nome}! 👋</h2>
                
                <p>Sua consulta foi agendada com <strong>sucesso</strong>!</p>
                
                <div class='info-box'>
                    <p><strong>📅 Data:</strong> {$data_consulta}</p>
                    <p><strong>🕐 Hora:</strong> {$hora}</p>
                    <p><strong>👨‍⚕️ Médico:</strong> {$medico}</p>
                    <p><strong>💉 Serviço:</strong> {$servico}</p>
                    " . (!empty($observacoes) ? "<p><strong>📝 Observações:</strong> {$observacoes}</p>" : "") . "
                </div>
                
                <h3>📋 Informações Importantes:</h3>
                <ul>
                    <li>Chegue com <strong>15 minutos de antecedência</strong></li>
                    <li>Traga <strong>documento de identificação</strong> e cartão de saúde</li>
                    <li>Para cancelamentos, contacte-nos com <strong>24h de antecedência</strong></li>
                    <li>Em caso de atraso, ligue para: <strong>(+351) 123 456 789</strong></li>
                </ul>
                
                <h3>📍 Localização:</h3>
                <p>
                    Rua da Saúde, 123<br>
                    1000-001 Lisboa<br>
                    Portugal
                </p>
                
                <p style='text-align: center;'>
                    <a href='https://www.google.com/maps' class='button'>Ver no Mapa</a>
                </p>
                
                <p><em>Aguardamos por si! Se tiver alguma questão, não hesite em contactar-nos.</em></p>
            </div>
            
            <div class='footer'>
                <p>DermaCare - Clínica Dermatológica</p>
                <p>Email: info@dermacare.pt | Tel: (+351) 123 456 789</p>
                <p>Este é um email automático, por favor não responda.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($email, $assunto, $mensagem);
}

/**
 * Envia email de cancelamento
 */
function enviarCancelamento($data) {
    $nome = $data['nome'] ?? 'Cliente';
    $email = $data['email'];
    $servico = $data['servico'] ?? 'Consulta';
    $data_consulta = $data['data'] ?? '';
    $hora = $data['hora'] ?? '';
    $motivo = $data['motivo'] ?? 'Não especificado';
    
    $assunto = '❌ Consulta Cancelada - DermaCare';
    
    $mensagem = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
            .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { background-color: white; padding: 30px; border-radius: 5px; margin-top: 20px; }
            .info-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            h1 { margin: 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🏥 DermaCare</h1>
                <p>Clínica Dermatológica</p>
            </div>
            
            <div class='content'>
                <h2>Olá, {$nome}!</h2>
                
                <p>Confirmamos o <strong>cancelamento</strong> da sua consulta:</p>
                
                <div class='info-box'>
                    <p><strong>📅 Data:</strong> {$data_consulta}</p>
                    <p><strong>🕐 Hora:</strong> {$hora}</p>
                    <p><strong>💉 Serviço:</strong> {$servico}</p>
                    <p><strong>📝 Motivo:</strong> {$motivo}</p>
                </div>
                
                <p>Esperamos vê-lo novamente em breve!</p>
                
                <p style='text-align: center;'>
                    <a href='http://localhost/PSI_M17_04_Solução Web/site/marcacao.html' class='button'>Agendar Nova Consulta</a>
                </p>
            </div>
            
            <div class='footer'>
                <p>DermaCare - Clínica Dermatológica</p>
                <p>Email: info@dermacare.pt | Tel: (+351) 123 456 789</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($email, $assunto, $mensagem);
}

/**
 * Envia lembrete de consulta
 */
function enviarLembrete($data) {
    $nome = $data['nome'] ?? 'Cliente';
    $email = $data['email'];
    $servico = $data['servico'] ?? 'Consulta';
    $medico = $data['medico'] ?? 'Médico';
    $data_consulta = $data['data'] ?? '';
    $hora = $data['hora'] ?? '';
    
    $assunto = '⏰ Lembrete: Consulta Amanhã - DermaCare';
    
    $mensagem = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f9f9f9; }
            .header { background-color: #ffc107; color: #333; padding: 20px; text-align: center; }
            .content { background-color: white; padding: 30px; border-radius: 5px; margin-top: 20px; }
            .info-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
            h1 { margin: 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>⏰ LEMBRETE</h1>
                <p>DermaCare</p>
            </div>
            
            <div class='content'>
                <h2>Olá, {$nome}!</h2>
                
                <p>Este é um lembrete da sua consulta <strong>amanhã</strong>:</p>
                
                <div class='info-box'>
                    <p><strong>📅 Data:</strong> {$data_consulta}</p>
                    <p><strong>🕐 Hora:</strong> {$hora}</p>
                    <p><strong>👨‍⚕️ Médico:</strong> {$medico}</p>
                    <p><strong>💉 Serviço:</strong> {$servico}</p>
                </div>
                
                <p><strong>📌 Não se esqueça:</strong></p>
                <ul>
                    <li>Chegar 15 minutos antes</li>
                    <li>Trazer documento de identificação</li>
                    <li>Trazer cartão de saúde</li>
                </ul>
                
                <p><em>Aguardamos por si!</em></p>
            </div>
            
            <div class='footer'>
                <p>DermaCare - Clínica Dermatológica</p>
                <p>Email: info@dermacare.pt | Tel: (+351) 123 456 789</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return enviarEmail($email, $assunto, $mensagem);
}

/**
 * Função auxiliar para enviar email via mail() do PHP
 * NOTA: Para produção, use PHPMailer ou similar com SMTP
 */
function enviarEmail($para, $assunto, $mensagem) {
    // Headers para HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . FROM_NAME . " <" . FROM_EMAIL . ">" . "\r\n";
    $headers .= "Reply-To: " . FROM_EMAIL . "\r\n";
    
    // Tentar enviar email
    $enviado = @mail($para, $assunto, $mensagem, $headers);
    
    if ($enviado) {
        return ['sucesso' => true];
    } else {
        // Para desenvolvimento, logar em arquivo
        error_log("ERRO ao enviar email para: {$para}");
        error_log("Assunto: {$assunto}");
        
        // Salvar em arquivo para debug
        $log = "[" . date('Y-m-d H:i:s') . "] Email para: {$para}\n";
        $log .= "Assunto: {$assunto}\n";
        $log .= "HTML: " . strip_tags($mensagem) . "\n\n";
        file_put_contents('emails_log.txt', $log, FILE_APPEND);
        
        return [
            'sucesso' => false,
            'erro' => 'Falha ao enviar email. Verifique configuração do servidor.'
        ];
    }
}

?>
