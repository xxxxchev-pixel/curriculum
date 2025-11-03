<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Página Não Encontrada | GomesTech</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            text-align: center;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .error-code {
            font-size: 120px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        h1 {
            font-size: 2em;
            margin-bottom: 15px;
        }
        
        p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: #FF6A00;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(255, 106, 0, 0.3);
        }
        
        .btn:hover {
            background: #ff8c33;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 106, 0, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .links {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        
        .links a {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.2s ease;
            font-size: 0.95em;
        }
        
        .links a:hover {
            opacity: 1;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .error-code {
                font-size: 80px;
            }
            
            h1 {
                font-size: 1.5em;
            }
            
            p {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Página Não Encontrada</h1>
        <p>Ups! A página que procuras não existe ou foi movida.</p>
        <a href="/gomestech/" class="btn">Voltar ao Início</a>
        
        <div class="links">
            <a href="/gomestech/categorias/catalogo.php">Ver Catálogo</a>
            <a href="/gomestech/ajuda.php">Ajuda</a>
            <a href="/gomestech/conta.php">Minha Conta</a>
        </div>
    </div>
</body>
</html>
