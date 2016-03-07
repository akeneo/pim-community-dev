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
        array_merge($cDeps, ['Gedmo\Tree\RepositoryInterface']),
        RuleInterface::TYPE_ONLY,
        'The component classification should depends on nothing particular except Gedmo\Tree that is our base tree library.'
    ),
    new Rule('Akeneo\Component\Console', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule(
        'Akeneo\Component\FileStorage',
        array_merge(
            $cDeps,
            [
                'League\Flysystem',
                'Symfony\Component\HttpFoundation', // will be removed when we don't use Symfony uploaded files anymore
            ]
        ),
        RuleInterface::TYPE_ONLY,
        'The component file storage should depends on nothing particular except League\FlySystem that is our storage library.' .
        'For the moment we authorize HttpFoundation as we need it for file uploads :(.'
    ),
    new Rule('Akeneo\Component\Localization', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Component\StorageUtils', $cDeps, RuleInterface::TYPE_ONLY),
    new Rule('Akeneo\Component\Versioning', $cDeps, RuleInterface::TYPE_ONLY),
];

$cPimRules = [
    new Rule(
        'Pim\Component\Catalog',
        array_merge($cDeps, [
            'Akeneo\Component\Localization',
            'Akeneo\Component\FileStorage',
            'Akeneo\Component\Classification',
            'Akeneo\Component\Versioning',
            'Pim\Component\ReferenceData', //maybe should be merged inside catalog
        ]),
        RuleInterface::TYPE_ONLY
    )
];

$rules  = array_merge($cAkeneoRules, $cPimRules);
$config = new Configuration($rules, $finder);

return $config;
