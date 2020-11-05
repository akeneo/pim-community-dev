<?php
declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Property\TypedPropertyRector as TypedPropertyRectorAlias;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // here we can define, what sets of rules will be applied
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [SetList::CODE_QUALITY, SetList::SYMFONY_44]);

    // register single rule
    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRectorAlias::class);
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.4');

    $services = $containerConfigurator->services();
    $services->set(ClosureToArrowFunctionRector::class);
    $services->set(TypedPropertyRectorAlias::class);
};
