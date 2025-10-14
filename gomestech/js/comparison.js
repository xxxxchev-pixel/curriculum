fetch('data/produtos.json')
    .then(res => res.json())
    .then(produtos => {
        const container = document.getElementById("comparacao-container");
        container.innerHTML = produtos.map(p => `
            <div class="produto-comparacao">
                <img src="${p.imagem}" alt="${p.nome}" style="width:150px;">
                <h3>${p.nome}</h3>
                <p>Preço: €${p.preco}</p>
            </div>
        `).join('');
    });
