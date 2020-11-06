<?php
declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Property\TypedPropertyRector as TypedPropertyRectorAlias;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // here we can define, what sets of rules will be applied
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [SetList::CODE_QUALITY, SetList::SYMFONY_44]);
    $parameters->set(Option::PHP_VERSION_FEATURES, '7.4');
    $parameters->set(Option::EXCLUDE_PATHS, ['src/Oro']);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);
    $parameters->set(Option::EXCLUDE_PATHS, [
        __DIR__ . '/src/**/spec/*',
        __DIR__ . '/src/**/test/*',
        __DIR__ . '/src/**/tests/*',
        __DIR__ . '/src/**/Tests/*',
        __DIR__ . '/src/**/Test/*',
        __DIR__ . 'src/Oro/Bundle/DataGridBundle/Datasource/ResultRecord.php',
    ]);
    // Find rules here https://github.com/rectorphp/rector/blob/master/docs/rector_rules_overview.md
    $services = $containerConfigurator->services();
    $services->set(ClosureToArrowFunctionRector::class);
    $services->set(ReturnTypeDeclarationRector::class);
    $services->set(ParamTypeDeclarationRector::class);
};
