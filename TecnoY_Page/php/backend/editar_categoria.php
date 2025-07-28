<?php
// filepath: c:\xampp\htdocs\Proy2DS6\php\backend\editar_categoria.php
include '../backend/conexion.php';

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$imagen = null;

// Procesar imagen si se subió
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $imgTmp = $_FILES['imagen']['tmp_name'];
    $imgName = uniqid() . "_" . basename($_FILES['imagen']['name']);
    $carpeta = "../../image/img_categorias/";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }
    $destino = $carpeta . $imgName;
    if (move_uploaded_file($imgTmp, $destino)) {
        $imagen = "image/img_categorias/" . $imgName;
    }
}

if ($imagen) {
    $stmt = $conn->prepare("UPDATE categorias SET nombre=?, imagen=? WHERE id=?");
    $stmt->bind_param("ssi", $nombre, $imagen, $id);
} else {
    $stmt = $conn->prepare("UPDATE categorias SET nombre=? WHERE id=?");
    $stmt->bind_param("si", $nombre, $id);
}
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);