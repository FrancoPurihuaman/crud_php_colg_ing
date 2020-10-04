<?php include APP_PATH.'/resources/views/partials/head.php' ?>

<div class="container">

    <div class="row mt-3">
        <a href="<?php echo PUBLIC_PATH.'/users/create'?>" class="btn btn-default">Nuevo</a>
        <a href="<?php echo PUBLIC_PATH.'/users/pdf'?>" class="btn btn-default">Exportar a pdf</a>
        <a href="<?php echo PUBLIC_PATH.'/users/excel'?>" class="btn btn-default">Exportar a excel</a>
        <a href="<?php echo PUBLIC_PATH.'/users/chart'?>" class="btn btn-default">Grafico</a>
        <a href="<?php echo PUBLIC_PATH.'/panel/goOut'?>" class="btn btn-default">Salir</a>
    </div>

    <a href="<?php echo PUBLIC_PATH?>" class="d-block mt-2">Ver todas las rutas</a>

    <div class="row">
        <div class="title">
            <h1>USUARIOS</h1>
        </div>
    </div>

    <div class="">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Grupo</th>
                    <th colspan="2">Acciones</th>
                </tr>
                <tbody>
                    <?php 
                        if(!empty($users)){
                            foreach($users as $user){
                    ?>
                        <tr>
                            <td><?php echo $user->USU_ID?></td>
                            <td><?php echo $user->USU_NOMBRE?></td>
                            <td><?php echo $user->USU_USER?></td>
                            <td><?php echo $user->UG_NOMBRE?></td>
                            <td><a href="<?php echo PUBLIC_PATH ?>/users/update?id=<?php echo $user->USU_ID ?>">Editar</a></td>
                            <td><a href="<?php echo PUBLIC_PATH ?>/users/delete?id=<?php echo $user->USU_ID ?>">Borrar</a></td>
                        </tr>
                    <?php 
                            }
                        }
                    ?>    
                </tbody>
            </thead>
        </table>
    </div>
</div>


<?php include APP_PATH.'/resources/views/partials/footer.php' ?>