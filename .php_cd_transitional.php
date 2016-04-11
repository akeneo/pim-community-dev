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

$cPimEERules = [
    new Rule(
        'PimEnterprise\Component\Workflow',
        $cDeps,
        RuleInterface::TYPE_ONLY
    )
];

$config = new Configuration($cPimEERules, $finder);

return $config;
