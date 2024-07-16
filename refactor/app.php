<?php

namespace App;

require_once __DIR__ . '/vendor/autoload.php';

use App\Command\ImportCommand;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Proxy\ProxyFactory;
use Symfony\Component\Console\Application;

$paths = ['./Entity'];
$isDevMode = false;

// the connection configuration
$connectionParams = [
    'dbname' => 'globus',
    'user' => 'root',
    'password' => '',
    'host' => 'maria',
    'driver' => 'pdo_mysql',
];

$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);
$config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_ALWAYS);
$connection = DriverManager::getConnection($connectionParams, $config);
$entityManager = new EntityManager($connection, $config);

$application = new Application();

$application->add(new ImportCommand($entityManager));
$application->run();

