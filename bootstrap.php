<?php
use Doctrine\ORM\Tools\Setup;

$autoloader = require_once("vendor/autoload.php");

// Create a simple "default" Doctrine ORM configuration for XML Mapping
$isDevMode = true;
// $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
// or if you prefer yaml or annotations
//$config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

// database configuration parameters
$conn = array(
    'driver' => 'pdo_mysql',
    'dbname' => 'zf2_curso',
    // 'namespace' => 'AliceBootstrap',
    'user' => 'root'
);

// obtaining the entity manager
$entityManager = \Doctrine\ORM\EntityManager::create($conn, $config);

// configura para carregar entidades de config/php

$autoloader instanceof Composer\Autoload\ClassLoader ? $autoloader->add('', 'config/php') : null;
