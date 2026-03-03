<?php
require_once('controllers/cpag.php');
?>
<h2>Página</h2> <i class="fas fa-file"></i>
<form action="home.php?pg=<?=$pg?>" method="POST">
    <div class="row">
        <div class="form-group col-md-6">
        <label for="nompag">Nombre de la Página</label>
        <input type="text" name="nompag" id="nompag" class="form-control">
        </div>
        <div class="form-group col-md-6">
            <label for="despag">Descripción de la Página</label>
            <input type="text" name="despag" id="despag" class="form-control">
        </div>
                                <div class="form-group col-md-12">
                                <input type="hidden" name="idpag" value="<?php if($datOne &&$datOne[0]['idpag']) echo $datOne[0]['idpag']; ?>">
                                <input type="hidden" name="ope" value="save">
                                <br>
                                <input type="submit" class="form-control btn btn-dark" value="Enviar">
                            </div>
    </div>
</form>
<table id="table" class="table table-striped">
        <thead>
            <tr>
                <th>Nombre de la página</th>
                <th>Ruta Pagina</th>
                <th>Acciones</th>
                <th></th> 
            </tr>
        </thead>
        <tbody>
            <?php if($datAll){ foreach ($datAll AS $dt){ ?>
            <tr>
                <td><?=$dt['idpag']."-".$dt['nompag'];?></td>
                <td><?=$dt['ruta'];?></td>
                <td>
                    <a href="javascript:void(0);" onclick="editarPagina(<?=$dt['idpag'];?>);" title="Editar">
                    <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </a>
                    <a href="javascript:void(0);" onclick="deletePagina(<?=$dt['idpag'];?>);" title="Eliminar">
                        <i class="fa-solid fa-trash-can fa-2x"></i>
                    </a>
                </td>
            </tr>
            <?php }}?>
        </tbody>
        <thead>
            <tr>
                <th>Nombre de la página</th>
                <th>Ruta Pagina</th>
                <th>Acciones</th>
                <th></th>
            </tr>
        </thead>
    </table>

<script>
function editarPagina(idpag) {
    // Usar AJAX para cargar el formulario de edición sin perder sesión
    fetch('controllers/cpag.php?idpag=' + encodeURIComponent(idpag) + '&ope=edi')
        .then(response => response.text())
        .then(data => {
            // Si se obtiene correctamente, redirigir con AJAX para mantener sesión
            // O mejor aún, cargar en modal/sección
            // Por simplicidad, usar GET pero con session mantenida
            window.location.href = 'home.php?pg=<?=$pg;?>&idpag=' + encodeURIComponent(idpag) + '&ope=edi';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el formulario de edición');
        });
}

function deletePagina(idpag) {
    if (!confirm('¿Está seguro de que desea eliminar esta página?')) return;
    
    fetch('controllers/cpag_delete.php?idpag=' + encodeURIComponent(idpag))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Página eliminada');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'No se pudo eliminar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la página');
        });
}
</script>
