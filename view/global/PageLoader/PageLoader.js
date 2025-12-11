class PageLoader {
  constructor({ selector = '#page-loader', autoBindLoad = true } = {}) {
    this.el = document.querySelector(selector);
    this.onLoadHandler = this.onWindowLoad.bind(this);

    if (autoBindLoad) {
      if (document.readyState === 'complete') {
        // La página ya terminó de cargar
        this.onWindowLoad();
      } else {
        // Marca estado de carga y ocúltalo cuando termine
        document.documentElement.classList.add('is-loading');
        window.addEventListener('load', this.onLoadHandler);
      }
    }
  }

  onWindowLoad() {
    this.hide();
    window.removeEventListener('load', this.onLoadHandler);
  }

  set(on) {
    if (!this.el) return;
    this.el.classList.toggle('hide', !on);
    document.documentElement.classList.toggle('is-loading', on);
  }

  show() { this.set(true); }
  hide() { this.set(false); }
}


// Crea la instancia (oculta el loader automáticamente al terminar la carga)
const loader = new PageLoader({ selector: '#page-loader', autoBindLoad: true });

// Cuando quieras controlar manualmente:
//loader.show();
// ...tu tarea...
//loader.hide();
