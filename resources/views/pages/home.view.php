
<?php include APP_PATH.'/resources/views/partials/head.php' ?>

<div>
    <h1>Proyecto php - Curso PHP - Colegio de Ingenieros</h1>
    <h3>Rutas</h3>
    <ul>
    <?php if(!empty(ROUTES)){
        foreach(ROUTES as $ruta => $actions){ ?>
            <li><a href="<?php echo PUBLIC_PATH.'/'.$ruta?>"><?php echo PUBLIC_PATH.'/'.$ruta?></a></li>
    <?php }
        }
    ?>
    </ul>
</div>

<?php include APP_PATH.'/resources/views/partials/footer.php' ?>