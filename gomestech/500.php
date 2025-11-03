<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erro Interno | GomesTech</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f44336 0%, #e91e63 100%);
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
            background: white;
            color: #f44336;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1em;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .links {
            margin-top: 30px;
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
        <div class="error-code">500</div>
        <h1>Erro Interno do Servidor</h1>
        <p>Algo correu mal do nosso lado. A nossa equipa já foi notificada e está a trabalhar para resolver o problema.</p>
        <a href="/gomestech/" class="btn">Voltar ao Início</a>
        
        <div class="links">
            <a href="javascript:location.reload()">Tentar novamente</a>
        </div>
    </div>
</body>
</html>
