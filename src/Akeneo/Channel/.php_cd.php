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
        'Pim\Component\Catalog\Query\Filter\Operators', // TODO: It should be moved elsewhere
    ])->in('Akeneo\Channel\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Akeneo\Tool\Component',
        'Akeneo\Channel\Component',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface', // TODO: The channel is linked by reference instead of id
        'Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface', // TODO: The versioning bundle will be moved to Akeneo\Tool
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface', // TODO: we should remove this dependencies, related to permissions
        'Akeneo\UserManagement\Bundle\Context\UserContext', // TODO: The current local should be given by $context (third parameter of normalize) instead of depending on UserContext
        'Akeneo\Pim\Structure\Component\AttributeTypes', // TODO: Channels are currently tied to PIM_CATALOG_METRIC, to be able to convert units
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager', // TODO: Channels are currently tied to PIM_CATALOG_METRIC, to be able to convert units
    ])->in('Akeneo\Channel\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
