<?php
session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: ../frontend/landingPage.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Historial de Pedidos - TecnoY</title>
    <link rel="stylesheet" href="../../css/pedidos.css">
    <link rel="stylesheet" href="../../css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <main class="mainCont">
        <div class="genCont">
            <!-- Header de la página -->
            <div class="header-pedidos">
                <h1><i class="fas fa-history"></i> Mi Historial de Pedidos</h1>
                <div class="header-actions">
                    <button id="btn-actualizar" class="btn-actualizar">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <button id="btn-volver" class="btn-volver" onclick="window.location.href='landingPage.php'">
                        <i class="fas fa-arrow-left"></i> Volver al Inicio
                    </button>
                </div>
            </div>

            <!-- Resumen personal -->
            <div class="resumen-personal">
                <div class="stat-card">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-info">
                        <h3 id="mis-pedidos">0</h3>
                        <p>Mis Pedidos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="stat-info">
                        <h3 id="mis-gastos">$0.00</h3>
                        <p>Total Gastado</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar"></i>
                    <div class="stat-info">
                        <h3 id="ultimo-pedido">N/A</h3>
                        <p>Último Pedido</p>
                    </div>
                </div>
            </div>

            <!-- Filtros personales -->
            <div class="filtros-container">
                <div class="filtro-grupo">
                    <label for="filtro-fecha-personal">Filtrar por fecha:</label>
                    <input type="date" id="filtro-fecha-inicio-personal" placeholder="Fecha inicio">
                    <input type="date" id="filtro-fecha-fin-personal" placeholder="Fecha fin">
                </div>
                <div class="filtro-grupo">
                    <label for="filtro-monto">Filtrar por monto:</label>
                    <input type="number" id="filtro-monto-min" placeholder="Monto mínimo" step="0.01">
                    <input type="number" id="filtro-monto-max" placeholder="Monto máximo" step="0.01">
                </div>
                <button id="btn-limpiar-filtros-personal" class="btn-limpiar">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>

            <!-- Tabla de pedidos personales -->
            <div class="contTabla">
                <div class="tabla-header">
                    <h2>Mis Pedidos</h2>
                    <div class="loading-indicator" id="loading-pedidos-personal" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
                
                <table class="tablaPedidos">
                    <thead class="theadPedidos">
                        <tr>
                            <th class="thPedidos">ID Pedido</th>
                            <th class="thPedidos">Fecha y Hora</th>
                            <th class="thPedidos">Productos</th>
                            <th class="thPedidos">Estado</th>
                            <th class="thPedidos">Total</th>
                            <th class="thPedidos">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="tbodyPedidos" id="tabla-pedidos-personal-body">
                        <!-- Los pedidos se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
                
                <!-- Mensaje cuando no hay pedidos -->
                <div class="no-pedidos" id="no-pedidos-personal" style="display: none;">
                    <i class="fas fa-shopping-bag"></i>
                    <p>Aún no has realizado ningún pedido</p>
                    <button class="btn-comprar-ahora" onclick="window.location.href='landingPage.php'">
                        <i class="fas fa-shopping-cart"></i> Comprar Ahora
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para detalle de pedido personal -->
    <div id="modal-detalle-personal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-detalle">
            <div class="modal-header">
                <h2><i class="fas fa-receipt"></i> Detalle de mi Pedido</h2>
                <button type="button" class="modal-close-btn" onclick="cerrarModalDetallePersonal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detalle-info">
                    <div class="info-general">
                        <h3>Información del Pedido</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>ID Factura:</label>
                                <span id="detalle-id-factura-personal"></span>
                            </div>
                            <div class="info-item">
                                <label>Fecha:</label>
                                <span id="detalle-fecha-personal"></span>
                            </div>
                            <div class="info-item">
                                <label>Estado:</label>
                                <span id="detalle-estado-personal" class="estado-badge"></span>
                            </div>
                            <div class="info-item">
                                <label>Productos:</label>
                                <span id="detalle-cantidad-productos"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="productos-detalle">
                        <h3>Productos Comprados</h3>
                        <div id="productos-lista-personal"></div>
                    </div>
                    
                    <div class="totales-detalle">
                        <div class="total-linea">
                            <span>Subtotal:</span>
                            <span id="detalle-subtotal-personal">$0.00</span>
                        </div>
                        <div class="total-linea">
                            <span>ITBMS (7%):</span>
                            <span id="detalle-impuesto-personal">$0.00</span>
                        </div>
                        <div class="total-linea total-final">
                            <span>Total Pagado:</span>
                            <span id="detalle-total-personal">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-recomprar" onclick="recomprarProductos()">
                    <i class="fas fa-redo"></i> Recomprar
                </button>
                <button type="button" class="btn-cerrar" onclick="cerrarModalDetallePersonal()">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Sistema de notificaciones -->
    <div id="notifications-container"></div>

    <footer class="footCont">
        <!-- Footer content -->
    </footer>

    <script src="../../JS/notifications.js"></script>
    <script>
        // ============================================
        // VARIABLES GLOBALES
        // ============================================
        let pedidosPersonales = [];
        let pedidosPersonalesOriginal = [];
        let currentUser = '<?php echo $_SESSION['usuario']; ?>';

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            cargarPedidosPersonales();
            configurarEventListenersPersonales();
        });

        // ============================================
        // CONFIGURAR EVENT LISTENERS
        // ============================================
        function configurarEventListenersPersonales() {
            document.getElementById('btn-actualizar').addEventListener('click', cargarPedidosPersonales);
            document.getElementById('btn-limpiar-filtros-personal').addEventListener('click', limpiarFiltrosPersonales);
            
            // Filtros en tiempo real
            document.getElementById('filtro-fecha-inicio-personal').addEventListener('change', aplicarFiltrosPersonales);
            document.getElementById('filtro-fecha-fin-personal').addEventListener('change', aplicarFiltrosPersonales);
            document.getElementById('filtro-monto-min').addEventListener('input', aplicarFiltrosPersonales);
            document.getElementById('filtro-monto-max').addEventListener('input', aplicarFiltrosPersonales);
        }

        // ============================================
        // CARGAR PEDIDOS PERSONALES
        // ============================================
        async function cargarPedidosPersonales() {
            const loading = document.getElementById('loading-pedidos-personal');
            const tbody = document.getElementById('tabla-pedidos-personal-body');
            const noPedidos = document.getElementById('no-pedidos-personal');
            
            try {
                loading.style.display = 'block';
                tbody.innerHTML = '';
                noPedidos.style.display = 'none';
                
                const response = await fetch('../backend/CRUD/PEDIDOS/pedidosPersonal.php?accion=obtener');
                const data = await response.json();
                
                if (data.success) {
                    pedidosPersonales = data.pedidos;
                    pedidosPersonalesOriginal = [...data.pedidos];
                    mostrarPedidosPersonales(pedidosPersonales);
                    actualizarResumenPersonal(pedidosPersonales);
                    
                    if (pedidosPersonales.length === 0) {
                        noPedidos.style.display = 'block';
                    }
                    
                    notifications.show(`${data.totalPedidos} pedidos cargados`, 'success');
                } else {
                    notifications.show(data.mensaje || 'Error al cargar pedidos', 'error');
                    noPedidos.style.display = 'block';
                }
                
            } catch (error) {
                console.error('Error:', error);
                notifications.show('Error de conexión al cargar pedidos', 'error');
                noPedidos.style.display = 'block';
            } finally {
                loading.style.display = 'none';
            }
        }

        // ============================================
        // MOSTRAR PEDIDOS PERSONALES EN TABLA
        // ============================================
        function mostrarPedidosPersonales(pedidos) {
            const tbody = document.getElementById('tabla-pedidos-personal-body');
            tbody.innerHTML = '';
            
            pedidos.forEach(pedido => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="tdPedidos">
                        <strong>${pedido.idFactura}</strong>
                    </td>
                    <td class="tdPedidos">
                        <div class="fecha-info">
                            <strong>${formatearFecha(pedido.fecha)}</strong>
                            <small>${pedido.hora}</small>
                        </div>
                    </td>
                    <td class="tdPedidos">
                        <span class="productos-count">${pedido.totalProductos} producto${pedido.totalProductos > 1 ? 's' : ''}</span>
                    </td>
                    <td class="tdPedidos">
                        <span class="estado-badge estado-${pedido.estado.toLowerCase()}">
                            ${pedido.estado}
                        </span>
                    </td>
                    <td class="tdPedidos">
                        <strong class="precio-destacado">$${pedido.totalPedido.toFixed(2)}</strong>
                    </td>
                    <td class="tdPedidos">
                        <button class="btn-ver-detalle" onclick="verDetallePersonal('${pedido.idFactura}')">
                            <i class="fas fa-eye"></i> Ver Detalle
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ============================================
        // ACTUALIZAR RESUMEN PERSONAL
        // ============================================
        function actualizarResumenPersonal(pedidos) {
            const totalPedidos = pedidos.length;
            const totalGastado = pedidos.reduce((sum, pedido) => sum + pedido.totalPedido, 0);
            const ultimoPedido = pedidos.length > 0 ? formatearFecha(pedidos[0].fecha) : 'N/A';
            
            document.getElementById('mis-pedidos').textContent = totalPedidos;
            document.getElementById('mis-gastos').textContent = `$${totalGastado.toFixed(2)}`;
            document.getElementById('ultimo-pedido').textContent = ultimoPedido;
        }

        // ============================================
        // VER DETALLE PERSONAL
        // ============================================
        async function verDetallePersonal(idFactura) {
            try {
                const response = await fetch(`../backend/CRUD/PEDIDOS/pedidosPersonal.php?accion=detalle&idFactura=${idFactura}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarModalDetallePersonal(data);
                } else {
                    notifications.show(data.mensaje || 'Error al cargar detalle', 'error');
                }
                
            } catch (error) {
                console.error('Error:', error);
                notifications.show('Error de conexión al cargar detalle', 'error');
            }
        }

        // ============================================
        // MOSTRAR MODAL DE DETALLE PERSONAL
        // ============================================
        function mostrarModalDetallePersonal(data) {
            const modal = document.getElementById('modal-detalle-personal');
            const factura = data.factura;
            const productos = data.productos;
            const totales = data.totales;
            
            // Llenar información general
            document.getElementById('detalle-id-factura-personal').textContent = factura.idFactura;
            document.getElementById('detalle-fecha-personal').textContent = `${formatearFecha(factura.fecha)} ${factura.hora}`;
            document.getElementById('detalle-estado-personal').textContent = 'Completado';
            document.getElementById('detalle-estado-personal').className = 'estado-badge estado-completado';
            document.getElementById('detalle-cantidad-productos').textContent = `${productos.length} producto${productos.length > 1 ? 's' : ''}`;
            
            // Llenar productos
            const productosLista = document.getElementById('productos-lista-personal');
            productosLista.innerHTML = '';
            
            productos.forEach(producto => {
                const productoDiv = document.createElement('div');
                productoDiv.className = 'producto-detalle-item';
                productoDiv.innerHTML = `
                    <div class="producto-info">
                        <img src="../../${producto.imagen}" alt="${producto.nombProducto}" class="producto-img" 
                             onerror="this.src='../../images/default-product.png'">
                        <div class="producto-datos">
                            <h4>${producto.nombProducto}</h4>
                            <p>Marca: ${producto.marca_nombre || 'N/A'}</p>
                            <p>Precio unitario: $${parseFloat(producto.precioUnitario).toFixed(2)}</p>
                        </div>
                    </div>
                    <div class="producto-cantidad">
                        <span>Cantidad: ${producto.cantidad}</span>
                        <span class="precio-total">$${parseFloat(producto.precioTotal).toFixed(2)}</span>
                    </div>
                `;
                productosLista.appendChild(productoDiv);
            });
            
            // Llenar totales
            document.getElementById('detalle-subtotal-personal').textContent = `$${totales.subtotal.toFixed(2)}`;
            document.getElementById('detalle-impuesto-personal').textContent = `$${totales.impuesto.toFixed(2)}`;
            document.getElementById('detalle-total-personal').textContent = `$${totales.total.toFixed(2)}`;
            
            modal.style.display = 'flex';
        }

        // ============================================
        // CERRAR MODAL DE DETALLE PERSONAL
        // ============================================
        function cerrarModalDetallePersonal() {
            document.getElementById('modal-detalle-personal').style.display = 'none';
        }

        // ============================================
        // FUNCIONALIDAD DE RECOMPRA
        // ============================================
        function recomprarProductos() {
            // Obtener productos del modal actual
            const productosLista = document.getElementById('productos-lista-personal');
            const productos = productosLista.children;
            
            if (productos.length === 0) {
                notifications.show('No hay productos para recomprar', 'warning');
                return;
            }
            
            // Simular agregar productos al carrito
            notifications.show('Funcionalidad de recompra en desarrollo. Productos agregados a favoritos.', 'info');
            cerrarModalDetallePersonal();
        }

        // ============================================
        // APLICAR FILTROS PERSONALES
        // ============================================
        function aplicarFiltrosPersonales() {
            const fechaInicio = document.getElementById('filtro-fecha-inicio-personal').value;
            const fechaFin = document.getElementById('filtro-fecha-fin-personal').value;
            const montoMin = parseFloat(document.getElementById('filtro-monto-min').value) || 0;
            const montoMax = parseFloat(document.getElementById('filtro-monto-max').value) || Infinity;
            
            let pedidosFiltrados = [...pedidosPersonalesOriginal];
            
            // Filtrar por fecha
            if (fechaInicio) {
                pedidosFiltrados = pedidosFiltrados.filter(pedido => pedido.fecha >= fechaInicio);
            }
            
            if (fechaFin) {
                pedidosFiltrados = pedidosFiltrados.filter(pedido => pedido.fecha <= fechaFin);
            }
            
            // Filtrar por monto
            pedidosFiltrados = pedidosFiltrados.filter(pedido => 
                pedido.totalPedido >= montoMin && pedido.totalPedido <= montoMax
            );
            
            mostrarPedidosPersonales(pedidosFiltrados);
            actualizarResumenPersonal(pedidosFiltrados);
            
            const noPedidos = document.getElementById('no-pedidos-personal');
            if (pedidosFiltrados.length === 0) {
                noPedidos.style.display = 'block';
            } else {
                noPedidos.style.display = 'none';
            }
        }

        // ============================================
        // LIMPIAR FILTROS PERSONALES
        // ============================================
        function limpiarFiltrosPersonales() {
            document.getElementById('filtro-fecha-inicio-personal').value = '';
            document.getElementById('filtro-fecha-fin-personal').value = '';
            document.getElementById('filtro-monto-min').value = '';
            document.getElementById('filtro-monto-max').value = '';
            mostrarPedidosPersonales(pedidosPersonalesOriginal);
            actualizarResumenPersonal(pedidosPersonalesOriginal);
            document.getElementById('no-pedidos-personal').style.display = pedidosPersonalesOriginal.length === 0 ? 'block' : 'none';
        }

        // ============================================
        // FUNCIONES AUXILIARES
        // ============================================
        function formatearFecha(fecha) {
            return new Date(fecha).toLocaleDateString('es-ES');
        }

        // Cerrar modal al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                cerrarModalDetallePersonal();
            }
        });
    </script>
</body>
</html>