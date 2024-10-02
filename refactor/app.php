<?php

namespace App;

require_once __DIR__ . '/vendor/autoload.php';

use App\Command\ImportCommand;
use App\Config\Config;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Proxy\ProxyFactory;
use Symfony\Component\Console\Application;

// init configuration
Config::initialize();

// orm
$paths = ['./Entity'];
$isDevMode = false;

$config = ORMSetup::createAttributeMetadataConfiguration($paths, $isDevMode);
$config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_ALWAYS);
$connection = DriverManager::getConnection(Config::getDbConnParams(), $config);
$entityManager = new EntityManager($connection, $config);

// symfony console
$application = new Application();
$application->add(new ImportCommand($entityManager));
$application->run();

