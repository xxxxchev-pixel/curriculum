<?php
/**
 * API de Envio de Email de Lembrete de Consulta
 * 
 * Endpoint: /api/enviar-lembrete-consulta.php
 * Método: POST
 */

require_once 'config.php';

// Só aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderErro('Método não permitido. Use POST.', 405);
}

try {
    $dados = json_decode(file_get_contents('php://input'), true);
    
    if (empty($dados['email']) || empty($dados['nome'])) {
        responderErro('Email e nome são obrigatórios');
    }
    
    $email = $dados['email'];
    $nome = $dados['nome'];
    $medicoNome = $dados['medico_nome'] ?? 'Médico';
    $servicoNome = $dados['servico_nome'] ?? 'Consulta';
    $data = $dados['data'] ?? date('Y-m-d');
    $hora = $dados['hora'] ?? '10:00';
    
    $dataFormatada = formatarDataPT($data);
    $htmlEmail = gerarTemplateLembrete($nome, $medicoNome, $servicoNome, $dataFormatada, $hora);
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: DermaCare Clínica <noreply@dermacare.pt>',
        'Reply-To: contato@dermacare.pt',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $assunto = "🔔 Lembrete: Consulta Amanhã - DermaCare | {$hora}";
    
    $enviado = mail($email, $assunto, $htmlEmail, implode("\r\n", $headers));
    
    if ($enviado) {
        error_log("Email de lembrete enviado para: {$email}");
        responderSucesso(['email_enviado' => true], 'Lembrete enviado com sucesso!');
    } else {
        throw new Exception('Falha ao enviar lembrete');
    }
    
} catch (Exception $e) {
    error_log("Erro ao enviar lembrete: " . $e->getMessage());
    responderErro('Erro ao enviar lembrete: ' . $e->getMessage(), 500);
}

function formatarDataPT($data) {
    $timestamp = strtotime($data);
    $meses = [
        1 => 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
        'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
    ];
    $diasSemana = [
        'Sunday' => 'Domingo', 'Monday' => 'Segunda-feira', 'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira', 'Thursday' => 'Quinta-feira', 
        'Friday' => 'Sexta-feira', 'Saturday' => 'Sábado'
    ];
    
    $dia = date('d', $timestamp);
    $mes = $meses[(int)date('m', $timestamp)];
    $ano = date('Y', $timestamp);
    $diaSemana = $diasSemana[date('l', $timestamp)];
    
    return "{$diaSemana}, {$dia} de {$mes} de {$ano}";
}

function gerarTemplateLembrete($nome, $medico, $servico, $data, $hora) {
    return <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lembrete de Consulta - DermaCare</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f7fa; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600;">
                                <span style="font-size: 48px;">🔔</span><br>
                                Lembrete de Consulta
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Conteúdo -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px; font-size: 16px; color: #333; line-height: 1.6;">
                                Olá <strong>{$nome}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 16px; color: #333; line-height: 1.6;">
                                Este é um <strong style="color: #ff9800;">lembrete amigável</strong> sobre sua consulta <strong>amanhã</strong>! 📅
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Detalhes -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%); border-radius: 8px; padding: 25px; border: 2px solid #ffc107;">
                                <tr>
                                    <td>
                                        <h2 style="margin: 0 0 20px; font-size: 18px; color: #f57c00; font-weight: 600; text-align: center;">
                                            ⏰ Detalhes da Sua Consulta
                                        </h2>
                                        
                                        <table width="100%" cellpadding="12" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 6px;">
                                            <tr>
                                                <td style="font-size: 14px; color: #666; width: 140px;">
                                                    👨‍⚕️ <strong>Médico:</strong>
                                                </td>
                                                <td style="font-size: 14px; color: #333; font-weight: 600;">
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
                                                <td style="font-size: 15px; color: #f57c00; font-weight: 600;">
                                                    AMANHÃ - {$data}
                                                </td>
                                            </tr>
                                            <tr style="background-color: #fff3e0;">
                                                <td style="font-size: 14px; color: #666;">
                                                    ⏰ <strong>Horário:</strong>
                                                </td>
                                                <td style="font-size: 24px; color: #ff9800; font-weight: 700;">
                                                    {$hora}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Avisos Importantes -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <div style="background-color: #ffebee; border-left: 4px solid #f44336; padding: 15px; border-radius: 4px;">
                                <h3 style="margin: 0 0 10px; font-size: 14px; color: #c62828; font-weight: 600;">
                                    ⚠️ Não se esqueça!
                                </h3>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #c62828; line-height: 1.8;">
                                    <li>Chegue <strong>10 minutos antes</strong> (às {hora_chegada})</li>
                                    <li>Traga <strong>documento de identificação</strong></li>
                                    <li>Traga <strong>cartão do seguro</strong> (se aplicável)</li>
                                    <li>Se não puder comparecer, <strong>cancele com antecedência</strong></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Botões -->
                    <tr>
                        <td style="padding: 0 30px 30px; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 10px;">
                                        <a href="http://localhost/PSI_M17_04_Solução%20Web/site/minhas-marcacoes.html" 
                                           style="display: inline-block; background-color: #4caf50; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-size: 14px; font-weight: 600; margin: 5px;">
                                            ✅ Ver Detalhes
                                        </a>
                                        <a href="http://localhost/PSI_M17_04_Solução%20Web/site/minhas-marcacoes.html" 
                                           style="display: inline-block; background-color: #f44336; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 6px; font-size: 14px; font-weight: 600; margin: 5px;">
                                            ❌ Cancelar Consulta
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Localização -->
                    <tr>
                        <td style="padding: 0 30px 30px;">
                            <div style="background-color: #e8f5e9; border-radius: 8px; padding: 20px;">
                                <h3 style="margin: 0 0 10px; font-size: 16px; color: #2e7d32; font-weight: 600;">
                                    📍 Onde Estamos
                                </h3>
                                <p style="margin: 0; font-size: 14px; color: #333; line-height: 1.6;">
                                    <strong>DermaCare Clínica Dermatológica</strong><br>
                                    Av. da Liberdade, 123<br>
                                    1250-140 Lisboa, Portugal<br><br>
                                    📞 <a href="tel:+351213456789" style="color: #4caf50; text-decoration: none;">+351 21 345 6789</a>
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 25px; text-align: center; border-top: 1px solid #e9ecef;">
                            <p style="margin: 0 0 10px; font-size: 13px; color: #666;">
                                Estamos ansiosos para atendê-lo(a)! 💙
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                &copy; 2025 DermaCare. Todos os direitos reservados.
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
}
?>
