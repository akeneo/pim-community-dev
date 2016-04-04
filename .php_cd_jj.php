<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\Domain\Rule;
use Akeneo\CouplingDetector\Domain\RuleInterface;

$finder = new DefaultFinder();
$finder->notPath('Oro');
$finder->notPath('Acme');

$rules = [
    new Rule(
        'Pim\Component\Catalog',
        [
            /* business dependencies, Symfony components */
	    'Symfony\Component\Serializer',
    	    'Symfony\Component\Validator',
    	    'Symfony\Component\EventDispatcher',
    	    'Symfony\Component\Security\Core', // for the moment, let's discuss about that later
            /* utility dependencies */
	    'Symfony\Component\OptionsResolver',
	    'Symfony\Component\PropertyAccess',
	    'Symfony\Component\Filesystem',
	    'Symfony\Component\Yaml',
	    'Doctrine\Common\Collections',
	    'Doctrine\Common\Util\Inflector',
	    'Doctrine\Common\Util\ClassUtils',
	    'Doctrine\Common\Persistence\ObjectRepository',
	    'Akeneo\Component\StorageUtils',
	    /* specfic dependencies needed by this component */
    	    'Akeneo\Component\Localization',   // to localize product's data
            'Akeneo\Component\FileStorage',    // for product categories
            'Akeneo\Component\Classification', // to handle product's media
            'Akeneo\Component\Versioning',     // for the history of all models
        ],
        RuleInterface::TYPE_ONLY
    ),
];

$config = new Configuration($rules, $finder);

return $config;
