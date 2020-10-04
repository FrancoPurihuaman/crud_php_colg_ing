<?php 

define('ROUTES', [
    '' => ['controller' => 'Page', 'action' => 'index'],
    'access/login' => ['controller' => 'Access', 'action' => 'login'],
    'users/show' => ['controller' => 'Users', 'action' => 'show'],
    'users/create' => ['controller' => 'Users', 'action' => 'create'],
    'users/update' => ['controller' => 'Users', 'action' => 'update'],
    'users/delete' => ['controller' => 'Users', 'action' => 'destroy'],
    'users/pdf' => ['controller' => 'Users', 'action' => 'exportPdf'],
    'users/excel' => ['controller' => 'Users', 'action' => 'exportExcel'],
    'users/chart' => ['controller' => 'Users', 'action' => 'chart'],
    'panel' => ['controller' => 'Panel', 'action' => 'index'],
    'panel/goOut' => ['controller' => 'Panel', 'action' => 'goOut']
]);