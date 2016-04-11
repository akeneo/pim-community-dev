<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\Domain\Rule;
use Akeneo\CouplingDetector\Domain\RuleInterface;

$finder = new DefaultFinder();
$finder->notPath('Oro');
$finder->notPath('Acme');

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
    'Akeneo\Component\StorageUtils',
];

$cDeps = array_merge($cBusinessDeps, $cUtilsDeps);

$cAkeneoRules = [
    new Rule('Akeneo\Component\Analytics', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Component\Batch', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Component\Buffer', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule(
        'Akeneo\Component\Classification',
        array_merge($cDeps, [
            'Gedmo\Tree\RepositoryInterface', // used as base tree library
        ]),
        RuleInterface::TYPE_ONLY
    ),
    new Rule('Akeneo\Component\Console', $cDeps, RuleInterface::TYPE_ONLY, 'have no real existence, should be merged into Batch'),
    new Rule(
        'Akeneo\Component\FileStorage',
        array_merge($cDeps, [
            'League\Flysystem',  // used as base file storage system
        ]),
        RuleInterface::TYPE_ONLY
    ),
    new Rule('Akeneo\Component\Localization', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Component\StorageUtils', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Component\Versioning', $cDeps, RuleInterface::TYPE_ONLY),
];

$cPimRules = [
    new Rule(
        'Pim\Component\Catalog',
        array_merge($cDeps, [
            'Akeneo\Component\Localization',   // to localize product's data
            'Akeneo\Component\FileStorage',    // for product categories
            'Akeneo\Component\Classification', // to handle product's media
            'Akeneo\Component\Versioning',     // for the history of all models
        ]),
        RuleInterface::TYPE_ONLY
    ),
];

$rules  = array_merge($cAkeneoRules, $cPimRules);
$config = new Configuration($rules, $finder);

return $config;
