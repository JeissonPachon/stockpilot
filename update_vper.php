<?php
// Script temporal para reemplazar el archivo vper.php
$origen = 'c:\xampp\htdocs\stockpilot\stockpilot\views\vper_new.php';
$destino = 'c:\xampp\htdocs\stockpilot\stockpilot\views\vper.php';

if (file_exists($origen)) {
    // Hacer backup del archivo original
    copy($destino, $destino . '.bak');
    
    // Copiar el nuevo archivo
    copy($origen, $destino);
    
    // Eliminar el archivo temporal
    unlink($origen);
    
    echo "Archivo actualizado correctamente";
} else {
    echo "Error: archivo temporal no encontrado";
}
?>
