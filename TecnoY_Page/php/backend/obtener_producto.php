<?php
// filepath: c:\xampp\htdocs\Proy2DS6\php\backend\obtener_producto.php
include '../backend/conexion.php';

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT id, nombre, descripcion, precio, imagen, stock FROM productos WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();
$stmt->close();

echo json_encode($producto);