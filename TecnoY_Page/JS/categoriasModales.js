/**
 * GESTIÓN DE MODALES PARA CATEGORÍAS
 * Funciones para manejar modales de agregar, editar y eliminar categorías
 */

class CategoriasModales {
    constructor() {
        this.init();
    }

    init() {
        // Event listeners cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', () => {
            this.setupEventListeners();
        });
    }

    setupEventListeners() {
        // Formulario de edición
        const formEditarCategoria = document.getElementById('formEditarCategoria');
        if (formEditarCategoria) {
            formEditarCategoria.addEventListener('submit', (e) => this.handleEditSubmit(e));
        }

        // Preview de imagen al cambiar archivo
        const editImageInput = document.getElementById('edit-cat-imagen');
        if (editImageInput) {
            editImageInput.addEventListener('change', (e) => this.handleImagePreview(e));
        }

        // Botón confirmar eliminar
        const btnConfirmarEliminar = document.getElementById('btnConfirmarEliminar');
        if (btnConfirmarEliminar) {
            btnConfirmarEliminar.addEventListener('click', () => this.confirmarEliminar());
        }

        // Botón confirmar agregar
        const btnConfirmarAgregar = document.getElementById('btnConfirmarAgregar');
        if (btnConfirmarAgregar) {
            btnConfirmarAgregar.addEventListener('click', () => this.confirmarAgregar());
        }

        // Cerrar modales con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.cerrarTodosLosModales();
            }
        });

        // Cerrar modal al hacer click fuera del contenido
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('custom-modal')) {
                this.cerrarTodosLosModales();
            }
        });
    }

    // ========================================
    // MODAL DE AGREGAR CATEGORÍA
    // ========================================

    abrirModalAgregar() {
        console.log('Abriendo modal para agregar categoría');
        const modal = document.getElementById('modalConfirmarAgregar');
        if (modal) {
            modal.style.display = 'flex';
            // Validar que el formulario tenga datos
            const form = document.getElementById('form-categoria');
            const nombre = form.querySelector('input[name="nombre"]').value.trim();
            
            if (!nombre) {
                alert('Por favor, ingresa el nombre de la categoría');
                this.cerrarModalAgregar();
                return;
            }
        } else {
            console.error('Modal de agregar no encontrado');
        }
    }

    cerrarModalAgregar() {
        const modal = document.getElementById('modalConfirmarAgregar');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    confirmarAgregar() {
        const form = document.getElementById('form-categoria');
        if (form) {
            // Cambiar la acción del formulario para usar el CRUD
            form.action = '../backend/CRUD/CATEGORIA/agregarCat.php';
            form.method = 'POST';
            form.submit();
        }
        this.cerrarModalAgregar();
    }

    // ========================================
    // MODAL DE EDITAR CATEGORÍA
    // ========================================

    abrirModalEditar(id, nombre, imagen) {
        console.log('Abriendo modal para editar categoría ID:', id, 'Nombre:', nombre);
        
        const modal = document.getElementById('modalEditarCategoria');
        if (!modal) {
            console.error('Modal de edición no encontrado');
            return;
        }
        
        // Rellenar campos del formulario
        const idInput = document.getElementById('edit-cat-id');
        const nombreInput = document.getElementById('edit-cat-nombre');
        const preview = document.getElementById('edit-cat-preview');
        
        if (idInput) idInput.value = id || '';
        if (nombreInput) nombreInput.value = nombre || '';
        
        // Mostrar imagen preview si existe
        if (preview) {
            if (imagen && imagen.trim() !== '') {
                preview.src = '../../' + imagen;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
                preview.src = '';
            }
        }
        
        // Mostrar modal
        modal.style.display = 'flex';
        
        // Enfocar el campo nombre
        if (nombreInput) {
            setTimeout(() => nombreInput.focus(), 100);
        }
    }

    cerrarModalEditar() {
        const modal = document.getElementById('modalEditarCategoria');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    handleEditSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Mostrar indicador de carga
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Guardando...';
        submitBtn.disabled = true;
        
        fetch('../backend/CRUD/CATEGORIA/editCat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Categoría actualizada exitosamente');
                location.reload();
            } else {
                alert('Error al editar la categoría: ' + (data.mensaje || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión: ' + error.message);
        })
        .finally(() => {
            // Restaurar botón
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    handleImagePreview(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('edit-cat-preview');
        
        if (file && preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }

    // ========================================
    // MODAL DE ELIMINAR CATEGORÍA
    // ========================================

    abrirModalEliminar(id, nombre) {
        console.log('Abriendo modal para eliminar categoría ID:', id, 'Nombre:', nombre);
        
        const modal = document.getElementById('modalConfirmarEliminar');
        const mensaje = document.getElementById('modal-eliminar-mensaje');
        
        if (modal && mensaje) {
            mensaje.textContent = `¿Estás seguro de eliminar la categoría "${nombre}"?`;
            modal.style.display = 'flex';
            
            // Guardar el ID para usar en la confirmación
            this.idCategoriaEliminar = id;
        } else {
            console.error('Modal de confirmación de eliminación no encontrado');
        }
    }

    cerrarModalEliminar() {
        const modal = document.getElementById('modalConfirmarEliminar');
        if (modal) {
            modal.style.display = 'none';
        }
        this.idCategoriaEliminar = null;
    }

    confirmarEliminar() {
        if (this.idCategoriaEliminar) {
            // Redirigir para eliminar usando el parámetro GET
            window.location.href = '?eliminar=' + this.idCategoriaEliminar;
        }
        this.cerrarModalEliminar();
    }

    // ========================================
    // UTILIDADES
    // ========================================

    cerrarTodosLosModales() {
        const modales = document.querySelectorAll('.custom-modal');
        modales.forEach(modal => {
            modal.style.display = 'none';
        });
        this.idCategoriaEliminar = null;
    }

    // Función para cancelar operación y volver a página admin
    cancelarOperacion() {
        if (confirm('¿Estás seguro de cancelar la operación?')) {
            window.location.href = 'pag_adm.php';
        }
    }
}

// Crear instancia global
const categoriasModales = new CategoriasModales();

// Funciones globales para mantener compatibilidad con onclick en HTML
function abrirModalAgregarCategoria() {
    categoriasModales.abrirModalAgregar();
}

function cerrarModalAgregarCategoria() {
    categoriasModales.cerrarModalAgregar();
}

function abrirModalEditarCategoria(id, nombre, imagen) {
    categoriasModales.abrirModalEditar(id, nombre, imagen);
}

function cerrarModalEditarCategoria() {
    categoriasModales.cerrarModalEditar();
}

function mostrarModalEliminar(id, nombre) {
    categoriasModales.abrirModalEliminar(id, nombre);
}

function cerrarModalEliminar() {
    categoriasModales.cerrarModalEliminar();
}

function cancelarOperacion() {
    categoriasModales.cancelarOperacion();
}
