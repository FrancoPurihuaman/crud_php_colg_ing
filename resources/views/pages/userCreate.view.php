<?php include APP_PATH.'/resources/views/partials/head.php' ?>

<div class="container">
  <div class="row">
    <div class="content-create-user">
      <form class="form" action="<?php echo PUBLIC_PATH?>/users/create" method="POST">
          <h1>Crear Cuenta</h1>
          <div class="<?php echo isset($errors)?'bg-danger':''?>">
            <?php if(isset($errors)){ ?>
              <ul>
                <?php foreach($errors as $error){ ?>
                  <li><?php echo $error?></li>
                <?php }?>
              </ul>
            <?php } ?>
          </div>
          <div class="form-group">
            <input type="text" class="form-control caja__input" placeholder="Nombre de usuario" name="nombre" />
          </div>
          <div class="form-group">
            <input type="text" class="form-control caja__input" placeholder="Usuario" name="user" />
          </div>
          <div class="form-group">
            <input type="password" class="form-control caja__input" placeholder="Contraseña" name="pass" />
          </div>
          <div class="form-group">
          <input type="text" class="form-control caja__input" placeholder="Estado" name="estado" required="" />
          </div>
          <div class="form-group">
            <input type="text" class="form-control caja__input" placeholder="Activo" name="activo" required="" />
          </div>
          <div class="form-group">
            <select name="grupo" id="">
              <?php foreach($grupos as $grupo){?>
                <option value="<?php echo $grupo->UG_ID?>"><?php echo $grupo->UG_NOMBRE?></option>
              <?php }?>
            </select>
          </div>

          <div class="form-group text-center b-bottom">
            <input class="btn btn-default" type="submit" value="Crear">
          </div>

          <div class="clearfix"></div>

          <div class="form-group">
            <p class="change_link text-center">
              <a href="<?php echo PUBLIC_PATH;?>/users/show" class="to_register"> Ver todos los usuarios </a>
            </p>

            <div class="clearfix"></div>
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