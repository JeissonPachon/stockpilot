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
                   class="btn btn-sm <?=$dt['ver'] == 1 ? 'btn-success' : 'btn-outline-success';?>" role="button" aria-label="Toggle ver">
                    <i class="fa-solid fa-eye"></i>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'crear', <?=$dt['crear'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['crear'] == 1 ? 'btn-success' : 'btn-outline-success';?>" role="button" aria-label="Toggle crear">
                    <i class="fa-solid fa-plus"></i>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'editar', <?=$dt['editar'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['editar'] == 1 ? 'btn-success' : 'btn-outline-success';?>" role="button" aria-label="Toggle editar">
                    <i class="fa-solid fa-pencil"></i>
                </a>
            </td>
            <td>
                <a href="javascript:void(0);" onclick="cambiarPermiso(<?=$dt['idper'];?>, 'eliminar', <?=$dt['eliminar'] == 1 ? 0 : 1;?>)" 
                   class="btn btn-sm <?=$dt['eliminar'] == 1 ? 'btn-danger' : 'btn-outline-danger';?>" role="button" aria-label="Toggle eliminar">
                    <i class="fa-solid fa-trash"></i>
                </a>
            </td>
            <td><?=$dt['act'] == 1 ? 'Sí' : 'No';?></td>
            <td>
                <a href="index.php?pg=<?=$pg;?>&idper=<?=$dt['idper'];?>&ope=edi" title="Editar">
                    <i class="fa-solid fa-pen-to-square fa-2x"></i>
                </a>
                <a href="javascript:void(0);" onclick="openPagesModal(<?=$dt['idper'];?>)" title="Páginas">
                    <i class="fa-solid fa-list fa-2x"></i>
                </a>
                <a href="index.php?pg=<?=$pg;?>&idper=<?=$dt['idper'];?>&ope=eli" title="Eliminar" onclick="return eliminar();">
                    <i class="fa-solid fa-trash-can fa-2x"></i>
                </a>
            </td>
        </tr>
        <?php }} ?>
    </tbody>
</table>
<!-- Modal para mostrar páginas y toggles -->
<div id="pagesModal" class="modal" tabindex="-1" role="dialog" style="display:none;">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Páginas - Perfil</h5>
        <button type="button" class="close" onclick="closePagesModal();" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="pagesModalBody">
        <p>Cargando...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closePagesModal();">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
function cambiarPermiso(idper, permiso, valor) {
    fetch('controllers/cper_permiso_ajax.php?idper=' + encodeURIComponent(idper) + '&permiso=' + encodeURIComponent(permiso) + '&valor=' + encodeURIComponent(valor))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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

function openPagesModal(idper) {
    const modal = document.getElementById('pagesModal');
    const body = document.getElementById('pagesModalBody');
    body.innerHTML = '<p>Cargando...</p>';
    modal.style.display = 'block';

    fetch('controllers/cper_pages_list.php?idper=' + encodeURIComponent(idper))
        .then(resp => resp.json())
        .then(data => {
            if (!data.success) {
                body.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al cargar páginas') + '</div>';
                return;
            }
            const pages = data.pages;
            if (!pages || pages.length === 0) {
                body.innerHTML = '<p>No hay páginas disponibles.</p>';
                return;
            }
            let html = '<div class="list-group">';
            pages.forEach(p => {
                const checked = p.access == 1 ? 'checked' : '';
                html += '<label class="list-group-item">'
                    + '<input type="checkbox" data-idpag="' + p.idpag + '" ' + checked + ' onchange="togglePageAccess(' + idper + ', ' + p.idpag + ', this);"> '
                    + ' ' + p.nompag
                    + '</label>';
            });
            html += '</div>';
            body.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            body.innerHTML = '<div class="alert alert-danger">Error de comunicación</div>';
        });
}

function closePagesModal() {
    const modal = document.getElementById('pagesModal');
    modal.style.display = 'none';
}

function togglePageAccess(idper, idpag, checkbox) {
    checkbox.disabled = true;
    const form = new FormData();
    form.append('idper', idper);
    form.append('idpag', idpag);

    fetch('controllers/cper_toggle_page.php', { method: 'POST', body: form })
        .then(resp => resp.json())
        .then(data => {
            if (!data.success) {
                alert('Error: ' + (data.message || 'No se pudo cambiar el acceso'));
                // revert checkbox
                checkbox.checked = !checkbox.checked;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de comunicación al cambiar el acceso');
            checkbox.checked = !checkbox.checked;
        })
        .finally(() => {
            checkbox.disabled = false;
        });
}
</script>
