<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\Domain\Rule;
use Akeneo\CouplingDetector\Domain\RuleInterface;

$finder = new DefaultFinder();
$finder->notPath('Oro');
$finder->notPath('Acme');
$finder->notPath('spec');
$finder->notPath('tests');

$cBusinessDeps = [
    'Symfony\Component\Serializer',
    'Symfony\Component\Validator',
    'Symfony\Component\EventDispatcher',
    'Symfony\Component\Security\Core', // for the moment, let's discuss about that later
];
$cUtilsDeps    = [
    'Symfony\Component\OptionsResolver',
    'Symfony\Component\PropertyAccess',
    'Symfony\Component\Filesystem',
    'Symfony\Component\Yaml',
    'Doctrine\Common\Collections',
    'Doctrine\Common\Util\Inflector',
    'Doctrine\Common\Util\ClassUtils',
    'Doctrine\Common\Persistence\ObjectRepository',
    'Akeneo\Tool\Component\StorageUtils',
];

$cDeps = array_merge($cBusinessDeps, $cUtilsDeps);

$cAkeneoRules = [
    new Rule('Akeneo\Tool\Component\Analytics', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Tool\Component\Batch', array_merge($cDeps, [
        'Symfony\Component\Console'
    ]),
    RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Tool\Component\Buffer', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule(
        'Akeneo\Component\Classification',
        array_merge($cDeps, [
            'Gedmo\Tree\RepositoryInterface', // used as base tree library
        ]),
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'Akeneo\Tool\Component\Console',
        array_merge($cDeps, [
            'Symfony\Component\Process' // used in CommandLauncher
        ]),
        RuleInterface::TYPE_ONLY,
        'this component have no real existence, it should be merged into Batch'
    ),
    new Rule(
        'Akeneo\Component\FileStorage',
        array_merge($cDeps, [
            'League\Flysystem',  // used as base file storage system
            'Symfony\Component\HttpFoundation', // used to handle uploaded files & stream response
        ]),
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'Akeneo\Tool\Component\Localization',
            array_merge($cDeps, [
                'Symfony\Component\Translation', // to translate units of the metric attribute types
            ]),
            RuleInterface::TYPE_ONLY
    ),
    new Rule('Akeneo\Tool\Component\StorageUtils', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Tool\Component\Versioning', $cDeps, RuleInterface::TYPE_ONLY),
];

$cPimRules = [
/*
    new Rule(
        'Pim\Component\Catalog',
        array_merge($cDeps, [
            'Akeneo\Tool\Component\Localization',   // to localize product's data
            'Akeneo\Component\FileStorage',    // for product categories
            'Akeneo\Component\Classification', // to handle product's media
            'Akeneo\Tool\Component\Versioning',     // for the history of all models
        ]),
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'Pim\Component\Connector',
        array_merge($cDeps, [
            'Box\Spout',                     // to import/export CSV and XLSX files
            'Akeneo\Tool\Component\Batch',        // used as base import/export system
            'Akeneo\Tool\Component\Buffer',       // to handle large files
            'Akeneo\Component\FileStorage',  // to import/export product's media
            'Akeneo\Tool\Component\Localization', // to use date and number formats in configuration
            'Akeneo\Component\Classification', // to handle categories database reading
            'Pim\Component\Catalog',         // because ;)
            'Pim\Component\Localization',    // to check the localized data during an import
        ]),
        RuleInterface::TYPE_ONLY
    ),
*/

    new Rule(
        'Pim\Component\ReferenceData',
        array_merge($cDeps, [
            'Pim\Component\Catalog',         // because ;)
        ]),
        RuleInterface::TYPE_ONLY
    ),
//    new Rule('Pim\Component\User', $cDeps, RuleInterface::TYPE_ONLY),
];

$rules  = array_merge($cAkeneoRules, $cPimRules);
$config = new Configuration($rules, $finder);

return $config;
