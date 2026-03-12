<?php
require_once('controllers/cmod.php');
?>
<h2>Módulo</h2> <i class="fas fa-puzzle-piece"></i>
<form action="home.php?pg=<?=$pg?>" method="POST">
    <div class="row">
        <div class="form-group col-md-6">
        <label for="nommod">Nombre del Módulo</label>
        <input type="text" name="nommod" id="nommod" class="form-control">
        </div>
        <div class="form-group col-md-6">
            <label for="desmod">Descripción del Módulo</label>
            <input type="text" name="desmod" id="desmod" class="form-control">
        </div>
                                <div class="form-group col-md-12">
                                <input type="hidden" name="idmod" value="<?php if($datOne &&$datOne[0]['idmod']) echo $datOne[0]['idmod']; ?>">
                                <input type="hidden" name="ope" value="save">
                                <br>
                                <input type="submit" class="form-control btn btn-dark" value="Enviar">
                            </div>
    </div>
</form>
<div class="table-responsive">
<table id="table" class="table table-striped">
        <thead>
            <tr>
                <th>Nombre del módulo</th>
                <th>Id del Inventario</th>
                <th>Acciones</th>
                <th></th> 
            </tr>
        </thead>
        <tbody>
            <?php if($datAll){ foreach ($datAll AS $dt){ ?>
            <tr>
                <td><?=$dt['idmod']."-".$dt['nommod'];?></td>
                <td><?=$dt['idinv'];?></td>
                <td>
                    <a href="index.php?pg=<?=$pg;?>&idmod=<?=$dt['idmod'];?>&ope=edi" title="Editar">
                    <i class="fa-solid fa-pen-to-square fa-2x"></i>
                    </a>
                    <a href="index.php?pg=<?=$pg;?>&idmod=<?=$dt['idmod'];?>&ope=eli" title="Eliminar" onclick="return eliminar();">
                        <i class="fa-solid fa-trash-can fa-2x"></i>
                    </a>
                </td>
            </tr>
            <?php }}?>
        </tbody>
        <thead>
            <tr>
                <th>Nombre del módulo</th>
                <th>Id del Inventario</th>
                <th>Acciones</th>
                <th></th>
            </tr>
        </thead>
    </table>
</div>