<?php include APP_PATH.'/resources/views/partials/head.php' ?>

<div class="container">
  <div class="row">
    <div class="content-create-user">
      <form class="form" action="<?php echo PUBLIC_PATH?>/users/update" method="POST">
          <h1>Actualizar Usuario</h1>
          <div class="<?php echo isset($errors)?'bg-danger':''?>">
            <?php if(isset($errors)){ ?>
              <ul>
                <?php foreach($errors as $error){ ?>
                  <li><?php echo $error?></li>
                <?php }?>
              </ul>
            <?php } ?>
          </div>
          <div class="form-group hidden">
            <input type="hidden" class="form-control caja__input" placeholder="id" name="id" required="" value="<?php echo $data->USU_ID ?? '' ?>"/>
          </div>
          <div class="form-group">
            <input type="text" class="form-control caja__input" placeholder="Nombre de usuario" name="nombre" required="" value="<?php echo $data->USU_NOMBRE ?? '' ?>"/>
          </div>
          <div class="form-group">
            <input type="text" class="form-control caja__input" placeholder="Usuario" name="user" required="" value="<?php echo $data->USU_USER ?? '' ?>"/>
          </div>
          <div class="form-group">
            <input type="password" class="form-control caja__input" placeholder="Contraseña" name="pass" required="" value="<?php echo $data->USU_PASS ?? '' ?>"/>
          </div>
          <div class="form-group">
          <input type="text" class="form-control caja__input" placeholder="Estado" name="estado" required="" value="<?php echo $data->USU_ESTADO ?? '' ?>"/>
          </div>
          <div class="form-group">
            <input type="text" class="form-control caja__input" placeholder="Activo" name="activo" required="" value="<?php echo $data->USU_ACTIVO ?? '' ?>"/>
          </div>
          <div class="form-group">
            <select name="grupo" id="">
              <?php if(isset($data)){  //Asignar el grupo al recibir el 'id' por $_GET
                foreach($grupos as $grupo){?>
                <option value="<?php echo $grupo->UG_ID?>" <?php echo ($data->UG_ID == $grupo->UG_ID)?'selected':'' ?> ><?php echo $grupo->UG_NOMBRE?></option>
                <?php }
              }else{ 
                foreach($grupos as $grupo){?>
                  <option value="<?php echo $grupo->UG_ID?>"> <?php echo $grupo->UG_NOMBRE?></option>
                <?php }
              }?>
            </select>
          </div>

          <div class="form-group text-center b-bottom">
            <input class="btn btn-default" type="submit" value="Actualizar">
          </div>
          
          <div class="form-group">
            <p class="change_link text-center">
              <a href="<?php echo PUBLIC_PATH;?>/users/show" class="to_register"> Ver Usuarios </a>
            </p>

            <div class="clearfix"></div>
            <br />
          </div>

          <br />
        </div>
      </form>
    </div>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary hide <?php echo isset($msj)?'autoClick':''?>" data-toggle="modal" data-target="#exampleModal">
      Ver Modal
    </button>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"><?php echo isset($exito)?'Exito':'Error' ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <?php echo isset($msj)?$msj:'';?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include APP_PATH.'/resources/views/partials/footer.php' ?>