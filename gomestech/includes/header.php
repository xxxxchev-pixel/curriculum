<!-- Main Header -->
<header class="header-main" id="mainHeader">
    <div class="header-container">
        <!-- Logo -->
        <a href="index.php" class="logo">GomesTech</a>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Catálogo -->
            <a href="catalogo.php" class="header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                </svg>
                <span>Catálogo</span>
            </a>
            
            <!-- Comparação -->
            <a href="comparacao.php" class="header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 15v6M12 9v12M6 3v18"/>
                </svg>
                <span>Comparar</span>
            </a>
            
            <!-- Favoritos -->
            <a href="favoritos.php" class="header-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                </svg>
                <span>Favoritos</span>
            </a>
            
            <!-- User Account -->
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="conta.php" class="header-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span><?php echo htmlspecialchars(explode(' ', $_SESSION['user_nome'])[0]); ?></span>
                </a>
                <a href="logout.php" class="header-icon" style="color: #DC3545;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Sair</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="header-icon btn-auth">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span>Login e Registo</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>
