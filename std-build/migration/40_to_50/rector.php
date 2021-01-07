<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\String\ByteString;
use Rector\Renaming\Rector\Name\RenameClassRector;


/*
 * Test:  docker-compose run  -u www-data --rm php vendor/bin/rector -c std-build/migration/40_to_50/rector.php process ./std-build/migration/40_to_50/test-src
 */

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    if (isset($GLOBALS['argv'][4]) && preg_match("/test-src\$/", $GLOBALS['argv'][4])) { //Hack for enabling test mode...
        echo "Test mode";
        $parameters = $containerConfigurator->parameters();
        $parameters->set(Option::AUTOLOAD_PATHS, [$GLOBALS['argv'][4], "./src"]);
    }
    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => require(__DIR__ . '/renamed-classes.php'),
        ]]);
};
