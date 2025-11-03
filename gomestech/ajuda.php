<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuda - GomesTech</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <h1><a href="index.php" style="color: var(--accent); text-decoration: none;">GomesTech</a></h1>
                </div>
                
                <div class="header-actions">
                </div>
            </div>
        </div>
    </header>

    <!-- Help Section -->
    <section style="padding: 4rem 0; min-height: 70vh;">
        <div class="container">
            <h1 style="font-size: 2.5rem; margin-bottom: 2rem; color: var(--accent);">Central de Ajuda</h1>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                <div style="background: var(--card-bg); padding: 2rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                    <h3 style="color: var(--accent); margin-bottom: 1rem;">📞 Contactos</h3>
                    <p style="margin-bottom: 0.5rem;"><strong>Telefone:</strong> 800 123 456</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Email:</strong> info@gomestech.pt</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Horário:</strong> Seg-Sex: 9h-19h | Sáb: 10h-14h</p>
                </div>
                
                <div style="background: var(--card-bg); padding: 2rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                    <h3 style="color: var(--accent); margin-bottom: 1rem;">🚚 Entregas</h3>
                    <p style="margin-bottom: 0.5rem;"><strong>Portugal Continental:</strong> 24-48h</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Ilhas:</strong> 3-5 dias úteis</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Portes grátis:</strong> Compras > 50€</p>
                </div>
                
                <div style="background: var(--card-bg); padding: 2rem; border-radius: 12px; border-left: 4px solid var(--accent);">
                    <h3 style="color: var(--accent); margin-bottom: 1rem;">↩️ Devoluções</h3>
                    <p style="margin-bottom: 0.5rem;"><strong>Prazo:</strong> 14 dias</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Condição:</strong> Produto na embalagem original</p>
                    <p style="margin-bottom: 0.5rem;"><strong>Reembolso:</strong> 5-7 dias úteis</p>
                </div>
            </div>
            
            <div style="background: var(--card-bg); padding: 2rem; border-radius: 12px; margin-bottom: 2rem;">
                <h2 style="color: var(--accent); margin-bottom: 1.5rem;">❓ Perguntas Frequentes</h2>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: var(--text); margin-bottom: 0.5rem;">Como faço um pedido?</h4>
                    <p style="color: var(--text-muted);">Adicione os produtos ao carrinho, finalize a compra criando uma conta ou fazendo login, e siga as instruções de pagamento.</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: var(--text); margin-bottom: 0.5rem;">Quais os métodos de pagamento disponíveis?</h4>
                    <p style="color: var(--text-muted);">Aceitamos Multibanco, MBWay, Cartão de Crédito (Visa, Mastercard) e PayPal.</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: var(--text); margin-bottom: 0.5rem;">Posso alterar ou cancelar o meu pedido?</h4>
                    <p style="color: var(--text-muted);">Sim, até 1 hora após a compra. Contacte o nosso apoio ao cliente imediatamente.</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: var(--text); margin-bottom: 0.5rem;">Os produtos têm garantia?</h4>
                    <p style="color: var(--text-muted);">Sim, todos os produtos têm garantia do fabricante de 2 anos (mínimo legal).</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="color: var(--text); margin-bottom: 0.5rem;">Como acompanho o meu pedido?</h4>
                    <p style="color: var(--text-muted);">Após a compra, receberá um email com o código de rastreamento. Pode também consultar na área <a href="encomendas.php" style="color: var(--accent);">Encomendas</a>.</p>
                </div>
            </div>
            
            <div style="text-align: center; background: var(--secondary-bg); padding: 2rem; border-radius: 12px;">
                <h3 style="color: var(--accent); margin-bottom: 1rem;">Não encontrou a resposta?</h3>
                <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Entre em contacto connosco através dos canais acima ou envie um email.</p>
                <a href="mailto:info@gomestech.pt" class="btn-primary">Enviar Email</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 GomesTech. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
