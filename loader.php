<?php
require(__DIR__.'/bootstrap.php');

$loader = new \Nelmio\Alice\Loader\Yaml();

$objects = $loader->load(__DIR__.'/fixtures/tables/usuario.yml');

// cria as tabelas no banco
$carregador = new AliceTeste\Fixture\Loader();
$carregador->setEM($entityManager);
$carregador->createSchema($objects);

$persister = new \Nelmio\Alice\ORM\Doctrine($entityManager);
$persister->persist($objects);

