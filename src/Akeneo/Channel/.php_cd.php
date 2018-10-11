<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\Channel',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface', // TODO: we should remove this dependencies, related to permissions
    ])->in('Akeneo\Channel\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Akeneo\Channel\Component',
        'Akeneo\Tool\Component',
        'Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface', //TODO: Add interface in component and disallow bundle
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager', // TODO: Interface in component and disallow bundle, Channels are currently tied to PIM_CATALOG_METRIC, to be able to convert units
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface', // TODO: Link by reference instead of id + Functionnal issue because a channel can't go to category
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface', // TODO: Related to permissions
        'Akeneo\UserManagement\Bundle\Context\UserContext', // TODO: The current locale should be given by $context (third parameter of normalize) instead of depending on UserContext
        'Akeneo\Pim\Structure\Component\AttributeTypes', // TODO: Channels are currently tied to PIM_CATALOG_METRIC, to be able to convert units, Functionnal issue
    ])->in('Akeneo\Channel\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
