document.addEventListener('DOMContentLoaded', () => {
  // Load comparison from localStorage if no ids in URL
  const params = new URLSearchParams(window.location.search);
  if (!params.has('ids') && window.location.pathname.includes('comparacao.php')) {
    const ids = localStorage.getItem('compare_ids') || '';
    if (ids) {
      window.location.search = '?ids=' + encodeURIComponent(ids);
    }
  }
  
  // Clear comparison button
  const clearBtn = document.getElementById('clearComparison');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      if (confirm('Tem certeza que deseja limpar toda a comparação?')) {
        localStorage.removeItem('compare_ids');
        window.location.href = 'comparacao.php';
      }
    });
  }
});
