function formatEuro(n){ return n.toFixed(2).replace('.',',') + ' €'; }
function applyDiscount(price, pct){ return +(price * (1 - pct/100)).toFixed(2); }
window.pricing = { formatEuro, applyDiscount };
