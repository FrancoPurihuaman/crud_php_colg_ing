<?php include APP_PATH.'/resources/views/partials/head.php' ?>

<div class="container">
  <div class="row">
    <div class="content-login-user">
      <form class="form" action="<?php echo PUBLIC_PATH?>/access/login" method="POST">
        <h1>Login Proyecto</h1>
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
          <input type="text" class="form-control caja__input" placeholder="Username" name="user" required="" />
        </div>
        <div class="form-group">
          <input type="password" class="form-control caja__input" placeholder="Password" name="pass" required="" />
        </div>
        <div class="form-group text-center b-bottom">
          <input class="btn btn-default" type="submit" value="Acceder">
        </div>

        <div class="clearfix"></div>
      </form>
    </div>

  </div>
</div>

  <?php include APP_PATH.'/resources/views/partials/footer.php' ?>