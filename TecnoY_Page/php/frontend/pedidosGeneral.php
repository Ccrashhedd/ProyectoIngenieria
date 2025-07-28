<?php
session_start();

// Verificar que el usuario esté logueado y sea admin
if (!isset($_SESSION['usuario']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    header('Location: ../frontend/landingPage.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - TecnoY Admin</title>
    <link rel="stylesheet" href="../../css/pedidos.css">
    <link rel="stylesheet" href="../../css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <main class="mainCont">
        <div class="genCont">
            <!-- Header de la página -->
            <div class="header-pedidos">
                <h1><i class="fas fa-shopping-bag"></i> Gestión de Pedidos</h1>
                <div class="header-actions">
                    <button id="btn-estadisticas" class="btn-estadisticas">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </button>
                    <button id="btn-actualizar" class="btn-actualizar">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </div>
            </div>

            <!-- Resumen de estadísticas -->
            <div class="estadisticas-resumen" id="estadisticas-resumen" style="display: none;">
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="stat-info">
                        <h3 id="total-pedidos">0</h3>
                        <p>Total Pedidos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-dollar-sign"></i>
                    <div class="stat-info">
                        <h3 id="ventas-total">$0.00</h3>
                        <p>Ventas Totales</p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar"></i>
                    <div class="stat-info">
                        <h3 id="pedidos-mes">0</h3>
                        <p>Este Mes</p>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filtros-container">
                <div class="filtro-grupo">
                    <label for="filtro-fecha">Filtrar por fecha:</label>
                    <input type="date" id="filtro-fecha-inicio" placeholder="Fecha inicio">
                    <input type="date" id="filtro-fecha-fin" placeholder="Fecha fin">
                </div>
                <div class="filtro-grupo">
                    <label for="filtro-cliente">Buscar cliente:</label>
                    <input type="text" id="filtro-cliente" placeholder="Nombre o email del cliente">
                </div>
                <button id="btn-limpiar-filtros" class="btn-limpiar">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>

            <!-- Tabla de pedidos -->
            <div class="contTabla">
                <div class="tabla-header">
                    <h2>Lista de Pedidos</h2>
                    <div class="loading-indicator" id="loading-pedidos" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                    </div>
                </div>
                
                <table class="tablaPedidos">
                    <thead class="theadPedidos">
                        <tr>
                            <th class="thPedidos">ID Pedido</th>
                            <th class="thPedidos">Fecha</th>
                            <th class="thPedidos">Cliente</th>
                            <th class="thPedidos">Productos</th>
                            <th class="thPedidos">Estado</th>
                            <th class="thPedidos">Total</th>
                            <th class="thPedidos">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="tbodyPedidos" id="tabla-pedidos-body">
                        <!-- Los pedidos se cargarán aquí dinámicamente -->
                    </tbody>
                </table>
                
                <!-- Mensaje cuando no hay pedidos -->
                <div class="no-pedidos" id="no-pedidos" style="display: none;">
                    <i class="fas fa-shopping-bag"></i>
                    <p>No hay pedidos para mostrar</p>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para detalle de pedido -->
    <div id="modal-detalle" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-detalle">
            <div class="modal-header">
                <h2><i class="fas fa-receipt"></i> Detalle del Pedido</h2>
                <button type="button" class="modal-close-btn" onclick="cerrarModalDetalle()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detalle-info">
                    <div class="info-general">
                        <h3>Información General</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>ID Factura:</label>
                                <span id="detalle-id-factura"></span>
                            </div>
                            <div class="info-item">
                                <label>Fecha:</label>
                                <span id="detalle-fecha"></span>
                            </div>
                            <div class="info-item">
                                <label>Cliente:</label>
                                <span id="detalle-cliente"></span>
                            </div>
                            <div class="info-item">
                                <label>Email:</label>
                                <span id="detalle-email"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="productos-detalle">
                        <h3>Productos</h3>
                        <div id="productos-lista"></div>
                    </div>
                    
                    <div class="totales-detalle">
                        <div class="total-linea">
                            <span>Subtotal:</span>
                            <span id="detalle-subtotal">$0.00</span>
                        </div>
                        <div class="total-linea">
                            <span>ITBMS (7%):</span>
                            <span id="detalle-impuesto">$0.00</span>
                        </div>
                        <div class="total-linea total-final">
                            <span>Total:</span>
                            <span id="detalle-total">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cerrar" onclick="cerrarModalDetalle()">
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
        let pedidosData = [];
        let pedidosOriginal = [];

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            cargarPedidos();
            configurarEventListeners();
        });

        // ============================================
        // CONFIGURAR EVENT LISTENERS
        // ============================================
        function configurarEventListeners() {
            document.getElementById('btn-actualizar').addEventListener('click', cargarPedidos);
            document.getElementById('btn-estadisticas').addEventListener('click', toggleEstadisticas);
            document.getElementById('btn-limpiar-filtros').addEventListener('click', limpiarFiltros);
            
            // Filtros en tiempo real
            document.getElementById('filtro-cliente').addEventListener('input', aplicarFiltros);
            document.getElementById('filtro-fecha-inicio').addEventListener('change', aplicarFiltros);
            document.getElementById('filtro-fecha-fin').addEventListener('change', aplicarFiltros);
        }

        // ============================================
        // CARGAR PEDIDOS
        // ============================================
        async function cargarPedidos() {
            const loading = document.getElementById('loading-pedidos');
            const tbody = document.getElementById('tabla-pedidos-body');
            const noPedidos = document.getElementById('no-pedidos');
            
            try {
                loading.style.display = 'block';
                tbody.innerHTML = '';
                noPedidos.style.display = 'none';
                
                const response = await fetch('../backend/CRUD/PEDIDOS/pedidosGeneral.php?accion=obtener');
                const data = await response.json();
                
                if (data.success) {
                    pedidosData = data.pedidos;
                    pedidosOriginal = [...data.pedidos];
                    mostrarPedidos(pedidosData);
                    
                    if (pedidosData.length === 0) {
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
        // MOSTRAR PEDIDOS EN TABLA
        // ============================================
        function mostrarPedidos(pedidos) {
            const tbody = document.getElementById('tabla-pedidos-body');
            tbody.innerHTML = '';
            
            pedidos.forEach(pedido => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="tdPedidos">${pedido.idFactura}</td>
                    <td class="tdPedidos">${formatearFecha(pedido.fecha)} ${pedido.hora}</td>
                    <td class="tdPedidos">
                        <div class="cliente-info">
                            <strong>${pedido.cliente.nombre}</strong>
                            <small>${pedido.cliente.email}</small>
                        </div>
                    </td>
                    <td class="tdPedidos">${pedido.totalProductos}</td>
                    <td class="tdPedidos">
                        <span class="estado-badge estado-${pedido.estado.toLowerCase()}">
                            ${pedido.estado}
                        </span>
                    </td>
                    <td class="tdPedidos">$${pedido.totalPedido.toFixed(2)}</td>
                    <td class="tdPedidos">
                        <button class="btn-ver-detalle" onclick="verDetallePedido('${pedido.idFactura}')">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // ============================================
        // VER DETALLE DE PEDIDO
        // ============================================
        async function verDetallePedido(idFactura) {
            try {
                const response = await fetch(`../backend/CRUD/PEDIDOS/pedidosGeneral.php?accion=detalle&idFactura=${idFactura}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarModalDetalle(data);
                } else {
                    notifications.show(data.mensaje || 'Error al cargar detalle', 'error');
                }
                
            } catch (error) {
                console.error('Error:', error);
                notifications.show('Error de conexión al cargar detalle', 'error');
            }
        }

        // ============================================
        // MOSTRAR MODAL DE DETALLE
        // ============================================
        function mostrarModalDetalle(data) {
            const modal = document.getElementById('modal-detalle');
            const factura = data.factura;
            const productos = data.productos;
            const totales = data.totales;
            
            // Llenar información general
            document.getElementById('detalle-id-factura').textContent = factura.idFactura;
            document.getElementById('detalle-fecha').textContent = `${formatearFecha(factura.fecha)} ${factura.hora}`;
            document.getElementById('detalle-cliente').textContent = factura.nombre_usuario;
            document.getElementById('detalle-email').textContent = factura.email;
            
            // Llenar productos
            const productosLista = document.getElementById('productos-lista');
            productosLista.innerHTML = '';
            
            productos.forEach(producto => {
                const productoDiv = document.createElement('div');
                productoDiv.className = 'producto-detalle-item';
                productoDiv.innerHTML = `
                    <div class="producto-info">
                        <img src="../../${producto.imagen}" alt="${producto.nombProducto}" class="producto-img">
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
            document.getElementById('detalle-subtotal').textContent = `$${totales.subtotal.toFixed(2)}`;
            document.getElementById('detalle-impuesto').textContent = `$${totales.impuesto.toFixed(2)}`;
            document.getElementById('detalle-total').textContent = `$${totales.total.toFixed(2)}`;
            
            modal.style.display = 'flex';
        }

        // ============================================
        // CERRAR MODAL DE DETALLE
        // ============================================
        function cerrarModalDetalle() {
            document.getElementById('modal-detalle').style.display = 'none';
        }

        // ============================================
        // TOGGLE ESTADÍSTICAS
        // ============================================
        async function toggleEstadisticas() {
            const estadisticas = document.getElementById('estadisticas-resumen');
            
            if (estadisticas.style.display === 'none' || !estadisticas.style.display) {
                await cargarEstadisticas();
                estadisticas.style.display = 'flex';
            } else {
                estadisticas.style.display = 'none';
            }
        }

        // ============================================
        // CARGAR ESTADÍSTICAS
        // ============================================
        async function cargarEstadisticas() {
            try {
                const response = await fetch('../backend/CRUD/PEDIDOS/pedidosGeneral.php?accion=estadisticas');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.estadisticas;
                    document.getElementById('total-pedidos').textContent = stats.totalPedidos;
                    document.getElementById('ventas-total').textContent = `$${stats.ventasTotal.toFixed(2)}`;
                    
                    // Calcular pedidos del mes actual
                    const mesActual = new Date().toISOString().slice(0, 7);
                    const pedidosMes = stats.pedidosPorMes.find(mes => mes.mes === mesActual);
                    document.getElementById('pedidos-mes').textContent = pedidosMes ? pedidosMes.cantidad : 0;
                }
                
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
            }
        }

        // ============================================
        // APLICAR FILTROS
        // ============================================
        function aplicarFiltros() {
            const filtroCliente = document.getElementById('filtro-cliente').value.toLowerCase();
            const fechaInicio = document.getElementById('filtro-fecha-inicio').value;
            const fechaFin = document.getElementById('filtro-fecha-fin').value;
            
            let pedidosFiltrados = [...pedidosOriginal];
            
            // Filtrar por cliente
            if (filtroCliente) {
                pedidosFiltrados = pedidosFiltrados.filter(pedido => 
                    pedido.cliente.nombre.toLowerCase().includes(filtroCliente) ||
                    pedido.cliente.email.toLowerCase().includes(filtroCliente)
                );
            }
            
            // Filtrar por fecha
            if (fechaInicio) {
                pedidosFiltrados = pedidosFiltrados.filter(pedido => pedido.fecha >= fechaInicio);
            }
            
            if (fechaFin) {
                pedidosFiltrados = pedidosFiltrados.filter(pedido => pedido.fecha <= fechaFin);
            }
            
            mostrarPedidos(pedidosFiltrados);
            
            const noPedidos = document.getElementById('no-pedidos');
            if (pedidosFiltrados.length === 0) {
                noPedidos.style.display = 'block';
            } else {
                noPedidos.style.display = 'none';
            }
        }

        // ============================================
        // LIMPIAR FILTROS
        // ============================================
        function limpiarFiltros() {
            document.getElementById('filtro-cliente').value = '';
            document.getElementById('filtro-fecha-inicio').value = '';
            document.getElementById('filtro-fecha-fin').value = '';
            mostrarPedidos(pedidosOriginal);
            document.getElementById('no-pedidos').style.display = pedidosOriginal.length === 0 ? 'block' : 'none';
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
                cerrarModalDetalle();
            }
        });
    </script>
</body>
</html>