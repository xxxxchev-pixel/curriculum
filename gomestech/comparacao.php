<?php
session_start();
require_once __DIR__ . '/config.php';

$mysqli = db_connect();
$produtos = [];
$compare_ids = [];

if (isset($_GET['ids']) && !empty($_GET['ids'])) {
    $ids = array_map('intval', explode(',', $_GET['ids']));
    // Filtrar ids inválidos (<= 0) — assume-se ids positivos na BD
    $ids = array_values(array_filter($ids, function($v){ return $v > 0; }));
    $compare_ids = $ids;
} else {
    $compare_ids = [];
}

if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $query = "SELECT * FROM produtos WHERE id IN ($placeholders)";
    $stmt = $mysqli->prepare($query);
    $types = str_repeat('i', count($compare_ids));
    $stmt->bind_param($types, ...$compare_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparar Produtos - GomesTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/gomestech.css">
    <link rel="stylesheet" href="css/hamburger-menu.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .compare-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            flex: 1;
        }
        .compare-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .compare-header h1 {
            font-size: 36px;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        .compare-header p {
            color: var(--text-muted);
            font-size: 18px;
        }
        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin-top: 24px;
        }
        .btn-back, .btn-clear {
            padding: 12px 32px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-clear {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        .btn-clear:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }
        .compare-grid {
            display: grid;
            gap: 32px;
            margin: 40px 0;
        }
        .compare-grid.columns-1 { grid-template-columns: repeat(1, minmax(0,1fr)); }
        .compare-grid.columns-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .compare-grid.columns-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        @media (max-width: 1024px){
            .compare-grid.columns-3 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        }
        @media (max-width: 640px){
            .compare-grid.columns-2, .compare-grid.columns-3 { grid-template-columns: repeat(1, minmax(0,1fr)); }
        }
        .compare-card {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
                position: relative;
        }
        .compare-card:hover {
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
            transform: translateY(-8px);
            border-color: var(--color-primary);
        }
            .btn-remove-card {
                position: absolute;
                top: 16px;
                right: 16px;
                width: 36px;
                height: 36px;
                background: #dc3545;
                color: white;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                transition: all 0.3s;
                z-index: 10;
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }
            .btn-remove-card:hover {
                background: #c82333;
                transform: rotate(90deg) scale(1.1);
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.5);
            }
        .compare-card img {
            width: 100%;
            height: 240px;
            object-fit: contain;
            margin-bottom: 24px;
            border-radius: 12px;
            background: var(--bg-secondary);
            padding: 20px;
        }
        .compare-card h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: var(--text-primary);
            min-height: 60px;
            line-height: 1.4;
        }
        .compare-specs {
            display: grid;
            gap: 16px;
            margin-top: 20px;
            flex: 1;
        }
        .spec-row {
            display: flex;
            justify-content: space-between;
            padding: 14px 16px;
            background: var(--bg-secondary);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
        }
        .spec-row:hover {
            background: var(--border-color);
        }
        .spec-label {
            font-weight: 600;
            color: var(--text-muted);
        }
        .spec-value {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
        }
        .price {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-primary);
            margin-top: 24px;
            text-align: center;
            padding: 16px;
            background: linear-gradient(135deg, rgba(255, 106, 0, 0.1), rgba(255, 106, 0, 0.05));
            border-radius: 12px;
        }
        .btn-analyze {
            width: 100%;
            max-width: 600px;
            padding: 18px;
            background: linear-gradient(135deg, var(--color-primary), #ff8c00);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin: 0 auto;
            display: block;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 106, 0, 0.3);
        }
        .btn-analyze:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 106, 0, 0.5);
        }
        .empty-compare {
            text-align: center;
            padding: 100px 40px;
            background: var(--bg-card);
            border-radius: 24px;
            border: 3px dashed var(--border-color);
            margin: 60px auto;
            max-width: 600px;
        }
        .empty-compare h2 {
            font-size: 32px;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        .empty-compare p {
            font-size: 18px;
            color: var(--text-muted);
            margin-bottom: 32px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(8px);
        }
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 50px;
            max-width: 900px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 25px 80px rgba(0,0,0,0.4);
            animation: modalSlide 0.3s ease;
        }
        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal-close {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 44px;
            height: 44px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .modal-close:hover {
            background: #c82333;
            transform: rotate(90deg) scale(1.1);
        }
        .modal-header {
            margin-bottom: 40px;
            text-align: center;
        }
        .modal-header h2 {
            font-size: 36px;
            margin-bottom: 16px;
            color: var(--text-primary);
        }
        .winner-badge {
            display: inline-block;
            padding: 16px 32px;
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #000;
            border-radius: 50px;
            font-weight: 700;
            font-size: 20px;
            margin: 24px 0;
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.5);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .analysis-item {
            margin-bottom: 32px;
            padding: 24px;
            background: var(--bg-secondary);
            border-radius: 16px;
            border: 2px solid var(--border-color);
            transition: all 0.3s;
        }
        .analysis-item:hover {
            border-color: var(--color-primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .analysis-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            align-items: center;
        }
        .analysis-label {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }
        .analysis-percent {
            font-size: 24px;
            font-weight: 700;
            color: var(--color-primary);
        }
        .progress-bar {
            height: 32px;
            background: var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--color-primary), #ff8c00);
            transition: width 0.8s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 16px;
            color: white;
            font-weight: 700;
            font-size: 15px;
        }
        .reason-text {
            margin-top: 16px;
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
        }
        .footer {
            margin-top: auto;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="compare-container">
        <div class="compare-header">
            <h1>⚖️ Comparar Produtos</h1>
            <p>Analise as especificações lado a lado</p>
        </div>
        <?php if(empty($produtos)):?>
            <div class="empty-compare">
                <h2>📦 Nenhum produto selecionado</h2>
                <p>Adicione produtos à comparação a partir do catálogo</p>
                <a href="catalogo.php" class="btn-back" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">Ver Catálogo</a>
            </div>
        <?php else:?>
            <?php $colsClass = 'columns-'.min(max(count($produtos),1),3); ?>
            <div class="compare-grid <?php echo $colsClass; ?>">
                <?php foreach($produtos as $produto):?>
                    <div class="compare-card">
                            <button class="btn-remove-card" onclick="removeFromCompare(<?php echo $produto['id'];?>)" title="Remover da comparação">
                                ✕
                            </button>
                        <img src="<?php echo htmlspecialchars($produto['imagem'] ?? 'https://via.placeholder.com/300');?>" alt="<?php echo htmlspecialchars($produto['marca'] . ' ' . $produto['modelo']);?>">
                        <h3><?php echo htmlspecialchars($produto['marca'] . ' ' . $produto['modelo']);?></h3>
                        <div class="compare-specs">
                            <?php if(isset($produto['processador']) && $produto['processador']):?><div class="spec-row"><span class="spec-label">Processador</span><span class="spec-value"><?php echo htmlspecialchars($produto['processador']);?></span></div><?php endif;?>
                            <?php if(isset($produto['ram']) && $produto['ram']):?><div class="spec-row"><span class="spec-label">RAM</span><span class="spec-value"><?php echo htmlspecialchars($produto['ram']);?></span></div><?php endif;?>
                            <?php if(isset($produto['armazenamento']) && $produto['armazenamento']):?><div class="spec-row"><span class="spec-label">Armazenamento</span><span class="spec-value"><?php echo htmlspecialchars($produto['armazenamento']);?></span></div><?php endif;?>
                            <?php if(isset($produto['camara']) && $produto['camara']):?><div class="spec-row"><span class="spec-label">Câmara</span><span class="spec-value"><?php echo htmlspecialchars($produto['camara']);?></span></div><?php endif;?>
                            <?php if(isset($produto['bateria']) && $produto['bateria']):?><div class="spec-row"><span class="spec-label">Bateria</span><span class="spec-value"><?php echo htmlspecialchars($produto['bateria']);?></span></div><?php endif;?>
                            <?php if(isset($produto['ecrã']) && $produto['ecrã']):?><div class="spec-row"><span class="spec-label">Ecrã</span><span class="spec-value"><?php echo htmlspecialchars($produto['ecrã']);?></span></div><?php endif;?>
                            <div class="spec-row"><span class="spec-label">Categoria</span><span class="spec-value"><?php echo htmlspecialchars($produto['categoria']);?></span></div>
                        </div>
                        <div class="price">€<?php echo number_format($produto['preco'],2);?></div>
                    </div>
                <?php endforeach;?>
            </div>
            <?php if(count($produtos)>=2):?>
                <button class="btn-analyze" onclick="openAnalysis()">
                    🔍 Ver Análise Detalhada
                </button>
            <?php endif;?>
        <?php endif;?>
    </main>
    <div id="analysisModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeAnalysis()">✕</button>
            <div class="modal-header">
                <h2>🏆 Análise de Comparação</h2>
                <div class="winner-badge" id="winnerBadge">🥇 Vencedor: Produto X</div>
            </div>
            <div id="analysisContent"></div>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-bottom"><p>&copy; <?php echo date('Y');?> GomesTech. Todos os direitos reservados.</p></div>
    </footer>
    <script>
        const produtos=<?php echo json_encode($produtos);?>;
        // Normalizar dados para o analisador JS: garantir `nome` e `preco` existam
        (function(){
            for(let i=0;i<produtos.length;i++){
                const p = produtos[i];
                if(!p.nome) p.nome = ((p.marca||'') + ' ' + (p.modelo||'')).trim() || p.modelo || p.marca || 'Produto';
                // Garantir preço numérico
                p.preco = (p.preco !== undefined && p.preco !== null && p.preco !== '') ? parseFloat(p.preco) : 0;
                // Normalizar RAM/armazenamento se estiverem em strings como '8GB' -> extrair número
                if(p.ram && typeof p.ram === 'string'){
                    const m = p.ram.match(/(\d+)/);
                    if(m) p.ram = parseInt(m[1]);
                }
                if(p.armazenamento && typeof p.armazenamento === 'string'){
                    const m = p.armazenamento.match(/(\d+)/);
                    if(m) p.armazenamento = parseInt(m[1]);
                }
                // Câmara, bateria e processador remain as-is (processador length used as heuristic)
            }
        })();
        
        function openAnalysis(){
            if(produtos.length<2)return;
            const modal=document.getElementById('analysisModal');
            const content=document.getElementById('analysisContent');
            const winnerBadge=document.getElementById('winnerBadge');
            
            let analysis='';
            let scores={};
            produtos.forEach((p,i)=>scores[i]={total:0,details:{}});
            
            const specs=[
                {key:'processador',label:'Processador',getValue:v=>v?v.length:0},
                {key:'ram',label:'Memória RAM',getValue:v=>v?parseInt(v):0},
                {key:'armazenamento',label:'Armazenamento',getValue:v=>v?parseInt(v):0},
                {key:'camara',label:'Câmara',getValue:v=>v?parseInt(v):0},
                {key:'bateria',label:'Bateria',getValue:v=>v?parseInt(v):0},
                {key:'preco',label:'Melhor Preço',getValue:v=>v,invert:true}
            ];
            
            specs.forEach(spec=>{
                const values=produtos.map((p,i)=>({idx:i,val:spec.getValue(p[spec.key] || null)})).filter(v=>v.val);
                if(values.length===0)return;
                
                const max=Math.max(...values.map(v=>v.val));
                const min=Math.min(...values.map(v=>v.val));
                const range=max-min||1;
                
                values.forEach(v=>{
                    let score=spec.invert?((max-v.val)/range)*100:(v.val===max?100:((v.val-min)/range)*100);
                    scores[v.idx].details[spec.key]=score;
                    scores[v.idx].total+=score;
                });
                
                const bestIdx=spec.invert?values.reduce((a,b)=>a.val<b.val?a:b).idx:values.reduce((a,b)=>a.val>b.val?a:b).idx;
                const bestProduct=produtos[bestIdx];
                const percent=100;
                
                analysis+=`
                    <div class="analysis-item">
                        <div class="analysis-header">
                            <span class="analysis-label">${spec.label}</span>
                            <span class="analysis-percent">${percent}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width:${percent}%">${bestProduct.nome || ((bestProduct.marca||'') + ' ' + (bestProduct.modelo||''))}</div>
                        </div>
                        <div class="reason-text">
                            <strong>${bestProduct.nome || ((bestProduct.marca||'') + ' ' + (bestProduct.modelo||''))}</strong> tem vantagem com <strong>${bestProduct[spec.key] || ''}</strong>
                            ${spec.invert?'oferecendo o melhor custo-benefício':'sendo superior nesta categoria'}.
                        </div>
                    </div>
                `;
            });
            
            const winnerIdx=Object.keys(scores).reduce((a,b)=>scores[a].total>scores[b].total?a:b);
            const winner=produtos[winnerIdx];
            const winnerScore=Math.round((scores[winnerIdx].total/(specs.length*100))*100);
            
            winnerBadge.innerHTML=`🥇 Vencedor: ${winner.nome} (${winnerScore}% melhor)`;
            content.innerHTML=analysis;
            modal.classList.add('active');
        }
        
        function closeAnalysis(){
            document.getElementById('analysisModal').classList.remove('active');
        }
        
            function removeFromCompare(productId) {
                    // Animar o card que vai ser removido
                    const cards = document.querySelectorAll('.compare-card');
                    cards.forEach(card => {
                        const btn = card.querySelector('.btn-remove-card');
                        if (btn && btn.onclick.toString().includes(productId.toString())) {
                            card.style.transition = 'all 0.5s ease-out';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.8) translateY(-20px)';
                        }
                    });
            
                const ids = (localStorage.getItem('compare_ids') || '').split(',').filter(Boolean);
                const newIds = ids.filter(id => id !== productId.toString());
            
                    // Mostrar notificação
                    const notification = document.createElement('div');
                    notification.textContent = '✅ Produto removido da comparação';
                    notification.style.cssText = `
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: linear-gradient(135deg, #00ff88, #00cc6a);
                        color: #000;
                        padding: 16px 32px;
                        border-radius: 12px;
                        box-shadow: 0 8px 24px rgba(0, 255, 136, 0.4);
                        z-index: 10000;
                        font-weight: 600;
                        animation: slideInRight 0.3s ease-out;
                    `;
                    document.body.appendChild(notification);
            
                    setTimeout(() => {
                        notification.style.animation = 'slideOutRight 0.3s ease-out';
                        setTimeout(() => notification.remove(), 300);
                    }, 2000);
            
                    // Adicionar animações CSS
                    if (!document.getElementById('removeAnimations')) {
                        const style = document.createElement('style');
                        style.id = 'removeAnimations';
                        style.textContent = `
                            @keyframes slideInRight {
                                from { transform: translateX(400px); opacity: 0; }
                                to { transform: translateX(0); opacity: 1; }
                            }
                            @keyframes slideOutRight {
                                from { transform: translateX(0); opacity: 1; }
                                to { transform: translateX(400px); opacity: 0; }
                            }
                        `;
                        document.head.appendChild(style);
                    }
            
                    // Redirecionar após animação
                    setTimeout(() => {
                if (newIds.length > 0) {
                    localStorage.setItem('compare_ids', newIds.join(','));
                    window.location.href = 'comparacao.php?ids=' + newIds.join(',');
                } else {
                    localStorage.removeItem('compare_ids');
                    window.location.href = 'comparacao.php';
                }
                    }, 500);
            }
        
        window.onclick=function(e){
            const modal=document.getElementById('analysisModal');
            if(e.target===modal)closeAnalysis();
        }
    </script>
    <script src="js/comparison.js"></script>
    <script src="js/enhanced-interactions.js"></script>
</body>
</html>
