/**
 * Hero Carousel - GomesTech
 * Carrossel acessível com keyboard, swipe e drag support
 */

class HeroCarousel {
  constructor(element) {
    this.carousel = element;
    this.track = element.querySelector('.hero-carousel__track');
    this.slides = Array.from(element.querySelectorAll('.hero-slide'));
    this.prevBtn = element.querySelector('.hero-carousel__arrow--prev');
    this.nextBtn = element.querySelector('.hero-carousel__arrow--next');
    this.bulletsContainer = element.querySelector('.hero-carousel__bullets');
    
    this.currentIndex = 0;
    this.totalSlides = this.slides.length;
    
    // Touch/Drag state
    this.isDragging = false;
    this.startPos = 0;
    this.currentTranslate = 0;
    this.prevTranslate = 0;
    
    if (this.totalSlides < 2) {
      // Não há necessidade de carrossel
      if (this.prevBtn) this.prevBtn.style.display = 'none';
      if (this.nextBtn) this.nextBtn.style.display = 'none';
      if (this.bulletsContainer) this.bulletsContainer.style.display = 'none';
      return;
    }
    
    this.init();
  }
  
  init() {
    // Criar bullets
    this.createBullets();
    
    // Event listeners
    this.prevBtn?.addEventListener('click', () => this.prev());
    this.nextBtn?.addEventListener('click', () => this.next());
    
    // Keyboard navigation
    this.carousel.addEventListener('keydown', (e) => this.handleKeyboard(e));
    
    // Touch events
    this.track.addEventListener('touchstart', (e) => this.touchStart(e), { passive: true });
    this.track.addEventListener('touchmove', (e) => this.touchMove(e), { passive: true });
    this.track.addEventListener('touchend', () => this.touchEnd());
    
    // Mouse drag events
    this.track.addEventListener('mousedown', (e) => this.dragStart(e));
    this.track.addEventListener('mousemove', (e) => this.dragMove(e));
    this.track.addEventListener('mouseup', () => this.dragEnd());
    this.track.addEventListener('mouseleave', () => this.dragEnd());
    
    // Prevenir arrasto de imagem
    this.track.addEventListener('dragstart', (e) => e.preventDefault());
    
    // Mostrar primeiro slide
    this.goToSlide(0);
  }
  
  createBullets() {
    if (!this.bulletsContainer) return;
    
    this.slides.forEach((slide, index) => {
      const button = document.createElement('button');
      button.className = 'hero-carousel__bullet';
      button.setAttribute('aria-label', `Ir para slide ${index + 1}: ${slide.getAttribute('data-title') || 'Slide ' + (index + 1)}`);
      button.addEventListener('click', () => this.goToSlide(index));
      
      if (index === 0) {
        button.classList.add('is-active');
      }
      
      this.bulletsContainer.appendChild(button);
    });
    
    this.bullets = Array.from(this.bulletsContainer.querySelectorAll('.hero-carousel__bullet'));
  }
  
  goToSlide(index, animate = true) {
    if (index < 0 || index >= this.totalSlides) return;
    
    this.currentIndex = index;
    const offset = -index * 100;
    
    if (!animate || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      this.track.style.transition = 'none';
    } else {
      this.track.style.transition = '';
    }
    
    this.track.style.transform = `translateX(${offset}%)`;
    this.prevTranslate = offset;
    this.currentTranslate = offset;
    
    // Atualizar classes is-active
    this.slides.forEach((slide, i) => {
      slide.classList.toggle('is-active', i === index);
    });
    
    if (this.bullets) {
      this.bullets.forEach((bullet, i) => {
        bullet.classList.toggle('is-active', i === index);
      });
    }
    
    // Atualizar aria-live para screen readers
    this.announceSlide(index);
  }
  
  announceSlide(index) {
    const slide = this.slides[index];
    const title = slide.getAttribute('data-title') || `Slide ${index + 1}`;
    
    // Criar ou atualizar announcer
    let announcer = document.getElementById('carousel-announcer');
    if (!announcer) {
      announcer = document.createElement('div');
      announcer.id = 'carousel-announcer';
      announcer.className = 'sr-only';
      announcer.setAttribute('aria-live', 'polite');
      announcer.setAttribute('aria-atomic', 'true');
      document.body.appendChild(announcer);
    }
    
    announcer.textContent = `Slide ${index + 1} de ${this.totalSlides}: ${title}`;
  }
  
  next() {
    const nextIndex = (this.currentIndex + 1) % this.totalSlides;
    this.goToSlide(nextIndex);
  }
  
  prev() {
    const prevIndex = (this.currentIndex - 1 + this.totalSlides) % this.totalSlides;
    this.goToSlide(prevIndex);
  }
  
  handleKeyboard(e) {
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      this.prev();
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      this.next();
    } else if (e.key === 'Home') {
      e.preventDefault();
      this.goToSlide(0);
    } else if (e.key === 'End') {
      e.preventDefault();
      this.goToSlide(this.totalSlides - 1);
    }
  }
  
  // Touch Events
  touchStart(e) {
    this.isDragging = true;
    this.startPos = e.touches[0].clientX;
    this.track.classList.add('is-dragging');
  }
  
  touchMove(e) {
    if (!this.isDragging) return;
    
    const currentPosition = e.touches[0].clientX;
    const diff = currentPosition - this.startPos;
    const percentage = (diff / this.carousel.offsetWidth) * 100;
    
    this.currentTranslate = this.prevTranslate + percentage;
    this.track.style.transition = 'none';
    this.track.style.transform = `translateX(${this.currentTranslate}%)`;
  }
  
  touchEnd() {
    if (!this.isDragging) return;
    
    this.isDragging = false;
    this.track.classList.remove('is-dragging');
    
    const movedBy = this.currentTranslate - this.prevTranslate;
    
    // Se moveu mais de 15%, mudar de slide
    if (movedBy < -15 && this.currentIndex < this.totalSlides - 1) {
      this.next();
    } else if (movedBy > 15 && this.currentIndex > 0) {
      this.prev();
    } else {
      // Voltar ao slide atual
      this.goToSlide(this.currentIndex);
    }
  }
  
  // Mouse Drag Events
  dragStart(e) {
    this.isDragging = true;
    this.startPos = e.clientX;
    this.track.classList.add('is-dragging');
    this.track.style.cursor = 'grabbing';
  }
  
  dragMove(e) {
    if (!this.isDragging) return;
    
    e.preventDefault();
    const currentPosition = e.clientX;
    const diff = currentPosition - this.startPos;
    const percentage = (diff / this.carousel.offsetWidth) * 100;
    
    this.currentTranslate = this.prevTranslate + percentage;
    this.track.style.transition = 'none';
    this.track.style.transform = `translateX(${this.currentTranslate}%)`;
  }
  
  dragEnd() {
    if (!this.isDragging) return;
    
    this.isDragging = false;
    this.track.classList.remove('is-dragging');
    this.track.style.cursor = '';
    
    const movedBy = this.currentTranslate - this.prevTranslate;
    
    // Se moveu mais de 15%, mudar de slide
    if (movedBy < -15 && this.currentIndex < this.totalSlides - 1) {
      this.next();
    } else if (movedBy > 15 && this.currentIndex > 0) {
      this.prev();
    } else {
      // Voltar ao slide atual
      this.goToSlide(this.currentIndex);
    }
  }
}

// Inicializar todos os carrosséis na página
document.addEventListener('DOMContentLoaded', () => {
  const carousels = document.querySelectorAll('.hero-carousel');
  carousels.forEach(carousel => new HeroCarousel(carousel));
});
