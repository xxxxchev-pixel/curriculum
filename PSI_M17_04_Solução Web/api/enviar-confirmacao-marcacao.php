<?php
/**
 * API de Envio de Email de Confirmação de Marcação
 * 
 * Endpoint: /api/enviar-confirmacao-marcacao.php
 * Método: POST
 */

require_once 'config.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderErro('Método não permitido. Use POST.', 405);
}

try {
    // Obter dados do POST
    $dados = json_decode(file_get_contents('php://input'), true);
    
    // Validar dados obrigatórios
    if (empty($dados['email']) || empty($dados['nome'])) {
        responderErro('Email e nome são obrigatórios');
    }
    
    // Extrair dados
    $email = $dados['email'];
    $nome = $dados['nome'];
    $medicoNome = $dados['medico_nome'] ?? 'Médico';
    $servicoNome = $dados['servico_nome'] ?? 'Consulta';
    $data = $dados['data'] ?? date('Y-m-d');
    $hora = $dados['hora'] ?? '10:00';
    $observacoes = $dados['observacoes'] ?? '';
    
    // Formatar data para português
    $dataFormatada = formatarDataPT($data);
    
    // Template HTML do email
    $htmlEmail = gerarTemplateConfirmacao($nome, $medicoNome, $servicoNome, $dataFormatada, $hora, $observacoes);
    
    // Headers do email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: DermaCare Clínica <noreply@dermacare.pt>',
        'Reply-To: contato@dermacare.pt',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $assunto = "✅ Consulta Confirmada - DermaCare | {$dataFormatada} às {$hora}";
    
    // Enviar email
    $enviado = mail($email, $assunto, $htmlEmail, implode("\r\n", $headers));
    
    if ($enviado) {
        // Log de sucesso
        error_log("Email de confirmação enviado para: {$email}");
        
        responderSucesso([
            'email_enviado' => true,
            'destinatario' => $email,
            'data_envio' => date('Y-m-d H:i:s')
        ], 'Email de confirmação enviado com sucesso!');
    } else {
        throw new Exception('Falha ao enviar email');
    }
    
} catch (Exception $e) {
    error_log("Erro ao enviar email: " . $e->getMessage());
    responderErro('Erro ao enviar email de confirmação: ' . $e->getMessage(), 500);
}

/**
 * Formatar data para português
 */
function formatarDataPT($data) {
    $timestamp = strtotime($data);
    $meses = [
        1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    $diasSemana = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado'
    ];
    
    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('m', $timestamp)];
    $ano = date('Y', $timestamp);
    $diaSemana = $diasSemana[date('l', $timestamp)];
    
    return "{$diaSemana}, {$dia} de {$mes} de {$ano}";
}

/**
 * Gerar template HTML do email de confirmação
 */
function gerarTemplateConfirmacao($nome, $medico, $servico, $data, $hora, $observacoes) {
    return <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação de Consulta - DermaCare</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f7fa; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                <span style="font-size: 36px;">✅</span><br>
                                Consulta Confirmada!
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Saudação -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px; font-size: 16px; color: #333; line-height: 1.6;">
                                Olá <strong>{$nome}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 16px; color: #333; line-height: 1.6;">
                                Sua consulta foi <strong style="color: #667eea;">confirmada com sucesso</strong>! 🎉
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Detalhes da Consulta -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8f9fa; border-radius: 8px; padding: 25px;">
                                <tr>
                                    <td>
                                        <h2 style="margin: 0 0 20px; font-size: 18px; color: #667eea; font-weight: 600;">
                                            📋 Detalhes da Consulta
                                        </h2>
                                        
                                        <table width="100%" cellpadding="8" cellspacing="0" border="0">
                                            <tr>
                                                <td style="font-size: 14px; color: #666; width: 120px;">
                                                    👨‍⚕️ <strong>Médico:</strong>
                                                </td>
                                                <td style="font-size: 14px; color: #333;">
                                                    {$medico}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; color: #666;">
                                                    💊 <strong>Serviço:</strong>
                                                </td>
                                                <td style="font-size: 14px; color: #333;">
                                                    {$servico}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; color: #666;">
                                                    📅 <strong>Data:</strong>
                                                </td>
                                                <td style="font-size: 14px; color: #333;">
                                                    {$data}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size: 14px; color: #666;">
                                                    ⏰ <strong>Horário:</strong>
                                                </td>
                                                <td style="font-size: 16px; color: #667eea; font-weight: 600;">
                                                    {$hora}
                                                </td>
                                            </tr>
HTML;

    if (!empty($observacoes)) {
        $htmlEmail .= <<<HTML
                                            <tr>
                                                <td style="font-size: 14px; color: #666;" valign="top">
                                                    📝 <strong>Observações:</strong>
                                                </td>
                                                <td style="font-size: 14px; color: #555; font-style: italic;">
                                                    {$observacoes}
                                                </td>
                                            </tr>
HTML;
    }

    $htmlEmail .= <<<HTML
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Informações Importantes -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px;">
                                <h3 style="margin: 0 0 10px; font-size: 14px; color: #856404; font-weight: 600;">
                                    ⚠️ Informações Importantes
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #856404; line-height: 1.8;">
                                    <li>Por favor, chegue <strong>10 minutos antes</strong> do horário marcado</li>
                                    <li>Traga documento de identificação e cartão do seguro (se aplicável)</li>
                                    <li>Em caso de impossibilidade, cancele com <strong>24h de antecedência</strong></li>
                                    <li>Enviaremos um lembrete <strong>24 horas antes</strong> da consulta</li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Botões de Ação -->
                    <tr>
                        <td style="padding: 0 30px 30px; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 10px;">
                                        <a href="http://localhost/PSI_M17_04_Solução%20Web/site/minhas-marcacoes.html" 
                                           style="display: inline-block; background-color: #667eea; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-size: 14px; font-weight: 600;">
                                            📅 Ver Minhas Consultas
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Localização -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <div style="background-color: #e7f3ff; border-radius: 8px; padding: 20px;">
                                <h3 style="margin: 0 0 10px; font-size: 16px; color: #0066cc; font-weight: 600;">
                                    📍 Como Chegar
                                </h3>
                                <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.6;">
                                    <strong>DermaCare Clínica Dermatológica</strong><br>
                                    Av. da Liberdade, 123<br>
                                    1250-140 Lisboa, Portugal<br><br>
                                    📞 <a href="tel:+351213456789" style="color: #667eea; text-decoration: none;">+351 21 345 6789</a><br>
                                    ✉️ <a href="mailto:contato@dermacare.pt" style="color: #667eea; text-decoration: none;">contato@dermacare.pt</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; font-size: 13px; color: #666;">
                                Este email foi enviado automaticamente. Por favor, não responda.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                &copy; 2025 DermaCare. Todos os direitos reservados.
                            </p>
                            <p style="margin: 10px 0 0; font-size: 12px;">
                                <a href="#" style="color: #667eea; text-decoration: none; margin: 0 10px;">Política de Privacidade</a>
                                <span style="color: #ccc;">|</span>
                                <a href="#" style="color: #667eea; text-decoration: none; margin: 0 10px;">Termos de Uso</a>
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    return $htmlEmail;
}
?>
