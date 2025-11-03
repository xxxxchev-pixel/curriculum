/**
 * GomesTech - Hamburger Menu JS
 * Menu off-canvas com lazy-loading de marcas e accordion
 */

class HamburgerMenu {
  constructor() {
    this.toggle = document.querySelector('.hamburger-toggle');
    this.menu = document.querySelector('.hamburger-menu');
    this.backdrop = document.querySelector('.hamburger-backdrop');
    this.closeBtn = document.querySelector('.hamburger-close');
    this.content = document.querySelector('.hamburger-content');
    
    this.isOpen = false;
    this.focusableElements = [];
    this.firstFocusable = null;
    this.lastFocusable = null;
    
    this.init();
  }
  
  init() {
    if (!this.toggle || !this.menu || !this.backdrop) {
      console.warn('Hamburger menu elements not found');
      return;
    }
    
    // Event listeners
    this.toggle.addEventListener('click', () => this.open());
    this.closeBtn?.addEventListener('click', () => this.close());
    this.backdrop.addEventListener('click', () => this.close());
    
    // Teclado
    document.addEventListener('keydown', (e) => this.handleKeydown(e));
    
    // Carregar categorias
    this.loadCategories();
    
    // Setup accordion
    this.setupAccordion();
  }
  
  async loadCategories() {
    try {
  const response = await fetch('/gomestech/api/categories.php', {
        headers: {
          'Accept': 'application/json'
        }
      });
      
      if (!response.ok) {
        throw new Error('Erro ao carregar categorias');
      }
      
      const result = await response.json();
      
      if (result.success) {
        this.renderCategories(result.data);
      }
    } catch (error) {
      console.error('Erro ao carregar categorias:', error);
      this.content.innerHTML = '<p class="brand-empty">Erro ao carregar menu</p>';
    }
  }
  
  renderCategories(categories) {
    const list = document.createElement('ul');
    list.className = 'category-list';
    list.setAttribute('role', 'tree');
    
    categories.forEach(category => {
      const item = this.createCategoryItem(category);
      list.appendChild(item);
    });
    
    this.content.innerHTML = '';
    this.content.appendChild(list);
    
    // Setup accordion após renderizar
    this.setupAccordion();
  }
  
  createCategoryItem(category) {
    const li = document.createElement('li');
    li.className = 'category-item';
    li.setAttribute('role', 'treeitem');
    li.setAttribute('data-category-slug', category.slug);
    
    const button = document.createElement('button');
    button.className = 'category-button';
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-label', `Ver marcas em ${category.name}`);
    
    // Ícone
    const icon = document.createElement('span');
    icon.className = 'category-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = category.icon || '📦';
    
    // Nome
    const name = document.createElement('span');
    name.className = 'category-name';
    name.textContent = category.name;
    
    // Seta
    const arrow = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    arrow.setAttribute('class', 'category-arrow');
    arrow.setAttribute('viewBox', '0 0 16 16');
    arrow.setAttribute('aria-hidden', 'true');
    
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', 'M6 4L10 8L6 12');
    arrow.appendChild(path);
    
    button.appendChild(icon);
    button.appendChild(name);
    button.appendChild(arrow);
    
    // Lista de marcas (placeholder)
    const brandList = document.createElement('ul');
    brandList.className = 'brand-list';
    brandList.setAttribute('role', 'group');
    brandList.setAttribute('aria-label', `Marcas de ${category.name}`);
    
    li.appendChild(button);
    li.appendChild(brandList);
    
    return li;
  }
  
  setupAccordion() {
    const categoryButtons = this.content.querySelectorAll('.category-button');
    
    categoryButtons.forEach(button => {
      // Remove listeners anteriores (se houver)
      const newButton = button.cloneNode(true);
      button.parentNode.replaceChild(newButton, button);
      
      newButton.addEventListener('click', (e) => {
        e.preventDefault();
        this.toggleCategory(newButton);
      });
    });
  }
  
  async toggleCategory(button) {
    const categoryItem = button.closest('.category-item');
    const categorySlug = categoryItem.dataset.categorySlug;
    const brandList = categoryItem.querySelector('.brand-list');
    const isExpanded = button.getAttribute('aria-expanded') === 'true';
    
    if (isExpanded) {
      // Fechar
      button.setAttribute('aria-expanded', 'false');
      categoryItem.removeAttribute('data-expanded');
    } else {
      // Abrir
      button.setAttribute('aria-expanded', 'true');
      categoryItem.setAttribute('data-expanded', 'true');
      
      // Lazy-load marcas se ainda não carregadas
      if (!categoryItem.dataset.brandsLoaded) {
        await this.loadBrands(categorySlug, brandList);
        categoryItem.dataset.brandsLoaded = 'true';
      }
    }
    
    // Atualizar focusable elements
    this.updateFocusableElements();
  }
  
  async loadBrands(categorySlug, container) {
    // Mostrar loading
    container.innerHTML = '<li class="brand-loading">Carregando marcas...</li>';
    
    try {
  const response = await fetch(`/gomestech/api/brands.php?category=${categorySlug}`, {
        headers: {
          'Accept': 'application/json'
        }
      });
      
      if (!response.ok) {
        throw new Error('Erro ao carregar marcas');
      }
      
      const result = await response.json();
      
      if (result.success && result.data.length > 0) {
        this.renderBrands(result.data, categorySlug, container);
      } else {
        container.innerHTML = '<li class="brand-empty">Nenhuma marca disponível</li>';
      }
    } catch (error) {
      console.error('Erro ao carregar marcas:', error);
      container.innerHTML = '<li class="brand-empty">Erro ao carregar marcas</li>';
    }
  }
  
  renderBrands(brands, categorySlug, container) {
    container.innerHTML = '';
    
    brands.forEach(brand => {
      const li = document.createElement('li');
      li.className = 'brand-item';
      
      const link = document.createElement('a');
      link.className = 'brand-link';
      link.href = `/c/${categorySlug}/${brand.slug}`;
      link.textContent = brand.name;
      link.setAttribute('role', 'treeitem');
      
      li.appendChild(link);
      container.appendChild(li);
    });
  }
  
  open() {
    this.isOpen = true;
    this.menu.classList.add('open');
    this.backdrop.classList.add('visible');
    this.toggle.setAttribute('aria-expanded', 'true');
    
    // Prevenir scroll do body
    document.body.style.overflow = 'hidden';
    
    // Focus trap setup
    this.updateFocusableElements();
    
    // Focar no botão de fechar
    requestAnimationFrame(() => {
      this.closeBtn?.focus();
    });
  }
  
  close() {
    this.isOpen = false;
    this.menu.classList.remove('open');
    this.backdrop.classList.remove('visible');
    this.toggle.setAttribute('aria-expanded', 'false');
    
    // Restaurar scroll do body
    document.body.style.overflow = '';
    
    // Retornar foco ao toggle
    this.toggle.focus();
  }
  
  updateFocusableElements() {
    const selector = 'button:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])';
    this.focusableElements = Array.from(this.menu.querySelectorAll(selector));
    this.firstFocusable = this.focusableElements[0];
    this.lastFocusable = this.focusableElements[this.focusableElements.length - 1];
  }
  
  handleKeydown(e) {
    if (!this.isOpen) return;
    
    // Esc fecha o menu
    if (e.key === 'Escape') {
      e.preventDefault();
      this.close();
      return;
    }
    
    // Focus trap com Tab
    if (e.key === 'Tab') {
      if (this.focusableElements.length === 0) {
        e.preventDefault();
        return;
      }
      
      if (e.shiftKey) {
        // Shift+Tab: voltar
        if (document.activeElement === this.firstFocusable) {
          e.preventDefault();
          this.lastFocusable?.focus();
        }
      } else {
        // Tab: avançar
        if (document.activeElement === this.lastFocusable) {
          e.preventDefault();
          this.firstFocusable?.focus();
        }
      }
    }
  }
}

// Inicializar quando DOM carregar
document.addEventListener('DOMContentLoaded', () => {
  new HamburgerMenu();
});
