// ============================================
// SLIDER DE PRECIO PERSONALIZADO
// ============================================

class PriceSlider {
    constructor() {
        this.minPrice = 0;
        this.maxPrice = 2999;
        this.currentMin = 0;
        this.currentMax = 2999;
        
        this.init();
    }

    init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupSlider());
        } else {
            this.setupSlider();
        }
    }

    setupSlider() {
        const sliderContainer = document.getElementById('price-slider-container');
        if (!sliderContainer) return;

        // Crear los elementos del slider
        this.createSliderHTML();
        this.bindEvents();
        this.updateDisplay();
    }

    createSliderHTML() {
        const container = document.getElementById('price-slider-container');
        container.innerHTML = `
            <div class="price-slider-wrapper">
                <div class="price-display">
                    <span class="price-min">$<span id="price-min-value">${this.currentMin}</span></span>
                    <span class="price-separator">-</span>
                    <span class="price-max">$<span id="price-max-value">${this.currentMax}</span></span>
                </div>
                
                <div class="slider-container">
                    <div class="slider-track"></div>
                    <div class="slider-range" id="slider-range"></div>
                    <input type="range" 
                           id="slider-min" 
                           class="slider-input" 
                           min="${this.minPrice}" 
                           max="${this.maxPrice}" 
                           value="${this.currentMin}" 
                           step="1">
                    <input type="range" 
                           id="slider-max" 
                           class="slider-input" 
                           min="${this.minPrice}" 
                           max="${this.maxPrice}" 
                           value="${this.currentMax}" 
                           step="1">
                </div>
                
                <div class="price-inputs">
                    <div class="price-input-group">
                        <label for="manual-min">Mín:</label>
                        <input type="number" 
                               id="manual-min" 
                               class="manual-price-input" 
                               min="${this.minPrice}" 
                               max="${this.maxPrice}" 
                               value="${this.currentMin}">
                    </div>
                    <div class="price-input-group">
                        <label for="manual-max">Máx:</label>
                        <input type="number" 
                               id="manual-max" 
                               class="manual-price-input" 
                               min="${this.minPrice}" 
                               max="${this.maxPrice}" 
                               value="${this.currentMax}">
                    </div>
                </div>
            </div>
        `;
    }

    bindEvents() {
        const sliderMin = document.getElementById('slider-min');
        const sliderMax = document.getElementById('slider-max');
        const manualMin = document.getElementById('manual-min');
        const manualMax = document.getElementById('manual-max');

        // Eventos para los sliders
        sliderMin.addEventListener('input', (e) => {
            this.handleMinChange(parseInt(e.target.value));
        });

        sliderMax.addEventListener('input', (e) => {
            this.handleMaxChange(parseInt(e.target.value));
        });

        // Eventos para inputs manuales
        manualMin.addEventListener('change', (e) => {
            this.handleMinChange(parseInt(e.target.value) || this.minPrice);
        });

        manualMax.addEventListener('change', (e) => {
            this.handleMaxChange(parseInt(e.target.value) || this.maxPrice);
        });

        // Validación en tiempo real para inputs manuales
        manualMin.addEventListener('input', (e) => {
            this.validateInput(e.target, 'min');
        });

        manualMax.addEventListener('input', (e) => {
            this.validateInput(e.target, 'max');
        });
    }

    handleMinChange(value) {
        // Asegurar que el mínimo no sea mayor que el máximo
        if (value >= this.currentMax) {
            value = this.currentMax - 1;
        }
        
        // Asegurar que esté dentro del rango permitido
        if (value < this.minPrice) {
            value = this.minPrice;
        }

        this.currentMin = value;
        this.updateSliders();
        this.updateDisplay();
        this.triggerFilterUpdate();
    }

    handleMaxChange(value) {
        // Asegurar que el máximo no sea menor que el mínimo
        if (value <= this.currentMin) {
            value = this.currentMin + 1;
        }
        
        // Asegurar que esté dentro del rango permitido
        if (value > this.maxPrice) {
            value = this.maxPrice;
        }

        this.currentMax = value;
        this.updateSliders();
        this.updateDisplay();
        this.triggerFilterUpdate();
    }

    validateInput(input, type) {
        const value = parseInt(input.value);
        
        if (isNaN(value)) {
            input.style.borderColor = '#ff6b6b';
            return;
        }

        if (type === 'min' && value >= this.currentMax) {
            input.style.borderColor = '#ff6b6b';
            return;
        }

        if (type === 'max' && value <= this.currentMin) {
            input.style.borderColor = '#ff6b6b';
            return;
        }

        if (value < this.minPrice || value > this.maxPrice) {
            input.style.borderColor = '#ff6b6b';
            return;
        }

        input.style.borderColor = '#ddd';
    }

    updateSliders() {
        const sliderMin = document.getElementById('slider-min');
        const sliderMax = document.getElementById('slider-max');
        const manualMin = document.getElementById('manual-min');
        const manualMax = document.getElementById('manual-max');

        if (sliderMin) sliderMin.value = this.currentMin;
        if (sliderMax) sliderMax.value = this.currentMax;
        if (manualMin) manualMin.value = this.currentMin;
        if (manualMax) manualMax.value = this.currentMax;

        this.updateSliderRange();
    }

    updateSliderRange() {
        const range = document.getElementById('slider-range');
        if (!range) return;

        const minPercent = ((this.currentMin - this.minPrice) / (this.maxPrice - this.minPrice)) * 100;
        const maxPercent = ((this.currentMax - this.minPrice) / (this.maxPrice - this.minPrice)) * 100;

        range.style.left = `${minPercent}%`;
        range.style.width = `${maxPercent - minPercent}%`;
    }

    updateDisplay() {
        const minValue = document.getElementById('price-min-value');
        const maxValue = document.getElementById('price-max-value');

        if (minValue) minValue.textContent = this.formatPrice(this.currentMin);
        if (maxValue) maxValue.textContent = this.formatPrice(this.currentMax);

        this.updateSliderRange();
    }

    formatPrice(price) {
        return price.toLocaleString();
    }

    triggerFilterUpdate() {
        // Disparar evento personalizado para que landing page pueda escuchar
        const event = new CustomEvent('priceRangeChanged', {
            detail: {
                min: this.currentMin,
                max: this.currentMax
            }
        });
        document.dispatchEvent(event);
    }

    // Método público para obtener el rango actual
    getCurrentRange() {
        return {
            min: this.currentMin,
            max: this.currentMax
        };
    }

    // Método público para resetear el slider
    reset() {
        this.currentMin = this.minPrice;
        this.currentMax = this.maxPrice;
        this.updateSliders();
        this.updateDisplay();
        this.triggerFilterUpdate();
    }
}

// Inicializar el slider cuando se cargue la página
let priceSlider;
document.addEventListener('DOMContentLoaded', function() {
    priceSlider = new PriceSlider();
});

// Exportar para uso global
window.PriceSlider = PriceSlider;
