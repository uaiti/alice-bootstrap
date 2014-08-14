<?php
require(__DIR__.'/bootstrap.php');

$loader = new \Nelmio\Alice\Loader\Yaml();

$objects = $loader->load(__DIR__.'/fixtures/modules/alice_teste.yml');
// $objects = $loader->load(__DIR__.'/fixtures/tables/usuario.yml');
// var_dump($objects);die;
// cria as tabelas no banco
$carregador = new AliceBootstrap\Fixture\Loader();
$carregador->setEM($entityManager);
$carregador->createSchema($objects);

$carregador->populate($objects);


