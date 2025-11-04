<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Em Breve - Educação & Tecnologia</title>
    <style>
        /* Reset e configurações básicas */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #fff;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
            position: relative;
        }
        
        /* Animação do gradiente de fundo */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Overlay para melhor legibilidade */
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        
        /* Container principal */
        .container {
            max-width: 800px;
            padding: 2rem;
            z-index: 2;
            position: relative;
        }
        
        /* Logo */
        .logo {
            margin-bottom: 3rem;
        }
        
        .logo h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #fdbb2d, #b21f1f);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .logo p {
            font-size: 1.3rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        /* Mensagem principal */
        .message {
            margin-bottom: 3rem;
        }
        
        .message h2 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 2rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: 2px;
        }
        
        .message p {
            font-size: 1.5rem;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        /* Ícones flutuantes */
        .floating-icons {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }
        
        .icon {
            position: absolute;
            font-size: 2.5rem;
            opacity: 0.1;
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
        }
        
        /* Rodapé */
        footer {
            margin-top: 3rem;
            font-size: 1rem;
            opacity: 0.7;
            z-index: 2;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .logo h1 {
                font-size: 2.2rem;
            }
            
            .message h2 {
                font-size: 2.8rem;
            }
            
            .message p {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    
    <div class="floating-icons">
        <!-- Ícones relacionados à educação e tecnologia -->
        <div class="icon" style="top: 10%; left: 5%;">📚</div>
        <div class="icon" style="top: 20%; left: 90%;">💻</div>
        <div class="icon" style="top: 40%; left: 15%;">🔬</div>
        <div class="icon" style="top: 60%; left: 85%;">🌐</div>
        <div class="icon" style="top: 80%; left: 10%;">📱</div>
        <div class="icon" style="top: 30%; left: 70%;">🎓</div>
        <div class="icon" style="top: 70%; left: 30%;">🚀</div>
        <div class="icon" style="top: 15%; left: 50%;">🧠</div>
    </div>
    
    <div class="container">
        <div class="logo">
            <h1>SiGEsCol</h1>
            <p>Sistema de Gestão Escolar</p>
        </div>
        
        <div class="message">
            <h2>EM BREVE ESTAREMOS ONLINE</h2>
            <p>Estamos preparando uma plataforma revolucionária que une educação e tecnologia para transformar a maneira como aprendemos e ensinamos.</p>
        </div>
        
        <footer>
            <p>&copy; 2025 - SiGEscol. Todos os direitos reservados.</p>
        </footer>
    </div>
</body>
</html>