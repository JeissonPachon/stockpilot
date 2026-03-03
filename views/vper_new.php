<?php
require_once('controllers/cper.php');
?>
<h2>Perfil</h2> <i class="fas fa-user-circle"></i>

<form action="home.php?pg=<?=$pg?>" method="POST">
    <div class="row">
        <div class="form-group col-md-6">
            <label for="nomper">Nombre del Perfil</label>
            <input type="text" name="nomper" id="nomper" class="form-control" 
                   value="<?php if($datOne && isset($datOne[0]['nomper'])) echo $datOne[0]['nomper']; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="act">Activo</label>
            <select name="act" id="act" class="form-control">
                <option value="1" <?php if($datOne && $datOne[0]['act'] == 1) echo "selected"; ?>>Sí</option>
                <option value="0" <?php if($datOne && $datOne[0]['act'] == 0) echo "selected"; ?>>No</option>
            </select>
        </div>

        <div class="form-group col-md-12">
            <input type="hidden" name="idper" value="<?php if($datOne && isset($datOne[0]['idper'])) echo $datOne[0]['idper']; ?>">
            <input type="hidden" name="ope" value="save">
            <br>
            <input type="submit" class="form-control btn btn-dark" value="Guardar">
        </div>
    </div>
</form>

<hr>

<table id="table" class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Ver</th>
            <th>Crear</th>
            <th>Editar</th>
            <th>Eliminar</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if($datAll){ foreach ($datAll as $dt){ ?>
        <tr>
            <td><?=$dt['idper'];?></td>
            <td><?=$dt['nomper'];?></td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'ver', <?=$dt['ver'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['ver'] == 1 ? 'btn-success' : 'btn-outline-success';?>">
                    <i class="fa-solid fa-eye"></i>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'crear', <?=$dt['crear'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['crear'] == 1 ? 'btn-success' : 'btn-outline-success';?>">
                    <i class="fa-solid fa-plus"></i>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'editar', <?=$dt['editar'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['editar'] == 1 ? 'btn-success' : 'btn-outline-success';?>">
                    <i class="fa-solid fa-pencil"></i>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'eliminar', <?=$dt['eliminar'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['eliminar'] == 1 ? 'btn-danger' : 'btn-outline-danger';?>">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
            <td><?=$dt['act'] == 1 ? 'Sí' : 'No';?></td>
            <td>
                <a href="index.php?pg=<?=$pg;?>&idper=<?=$dt['idper'];?>&ope=edi" title="Editar">
                    <i class="fa-solid fa-pen-to-square fa-2x"></i>
                </a>
                <a href="index.php?pg=<?=$pg;?>&idper=<?=$dt['idper'];?>&ope=eli" title="Eliminar" onclick="return eliminar();">
                    <i class="fa-solid fa-trash-can fa-2x"></i>
                </a>
            </td>
        </tr>
        <?php }} ?>
    </tbody>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Ver</th>
            <th>Crear</th>
            <th>Editar</th>
            <th>Eliminar</th>
            <th>Activo</th>
            <th>Acciones</th>
        </tr>
    </thead>
</table>

<script>
function cambiarPermiso(idper, permiso, valor) {
    fetch('controllers/cper_permiso_ajax.php?idper=' + idper + '&permiso=' + permiso + '&valor=' + valor)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página sin redirección visible
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el permiso');
        });
}
</script>
