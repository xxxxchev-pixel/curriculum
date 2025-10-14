// Hero Slider
const slides = document.querySelectorAll("#hero-slider img");
let currentSlide = 0;
slides[currentSlide].classList.add("active");

setInterval(() => {
    slides[currentSlide].classList.remove("active");
    currentSlide = (currentSlide + 1) % slides.length;
    slides[currentSlide].classList.add("active");
}, 5000);

// Produto do Dia
const produtos = [
    {
        nome: "iPhone 15",
        preco: 1029,
        imagem: "https://www.apple.com/pt/iphone-15/images/overview/hero/hero__dvsxk0smh6qe_large_2x.jpg"
    },
    {
        nome: "MacBook Air M2",
        preco: 1399,
        imagem: "https://www.apple.com/pt/macbook-air-m2/images/overview/hero/hero_endframe__bsza6x4fldiq_large_2x.jpg"
    },
    {
        nome: "PlayStation 5",
        preco: 499,
        imagem: "https://www.playstation.com/content/dam/ps5/console/ps5-console-hero-01.jpg"
    },
    {
        nome: "Samsung QLED TV",
        preco: 1299,
        imagem: "https://images.samsung.com/is/image/samsung/p6pim/pt/qled-tv/qn90b-hero-image.jpg"
    }
];

const produtoDiaContainer = document.getElementById("produto-do-dia");
const produtoAleatorio = produtos[Math.floor(Math.random() * produtos.length)];
produtoDiaContainer.innerHTML = `
    <h2>Produto do Dia: ${produtoAleatorio.nome}</h2>
    <img src="${produtoAleatorio.imagem}" alt="${produtoAleatorio.nome}" style="width:300px;">
    <p>Preço: €${produtoAleatorio.preco}</p>
`;
