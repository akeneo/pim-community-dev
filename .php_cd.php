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
    'Pim\Component',
    'PimEnterprise\Component',
    'Akeneo\Component',
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

$cAkeneoEERules = [
    new Rule(
        'Akeneo\Component\FileMetadata',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'Akeneo\Component\FileTransformer',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'Akeneo\Component\RuleEngine',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
];

$cPimEERules = [
    /*
    new Rule(
        'PimEnterprise\Component\CatalogRule',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'PimEnterprise\Component\ProductAsset',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'PimEnterprise\Component\Security',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'PimEnterprise\Component\TeamworkAssistant',
        $cDeps,
        RuleInterface::TYPE_ONLY
    ),
    new Rule(
        'PimEnterprise\Component\Workflow',
        $cDeps,
        RuleInterface::TYPE_ONLY
    )*/
];

$rules  = array_merge($cAkeneoEERules, $cPimEERules);
$config = new Configuration($cPimEERules, $finder);

return $config;
