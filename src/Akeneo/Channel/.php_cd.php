<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('spec');
$finder->notPath('tests');

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\Channel',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',
        'Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface', // TODO: we should remove this dependencies, related to permissions
        'Pim\Component\Catalog\Query\Filter\Operators', // TODO: It should be moved elsewhere
    ])->in('Akeneo\Channel\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Akeneo\Tool\Component',
        'Akeneo\Channel\Component',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface', // TODO: The channel is linked by reference instead of id
        'Pim\Component\Connector', // TODO: Generic classes/interfaces like be moved to Akeneo/Tool
        'Pim\Bundle\VersioningBundle\Repository\VersionRepositoryInterface', // TODO: The versioning bundle will be moved to Akeneo\Tool
        'Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface', // TODO: we should remove this dependencies, related to permissions
        'Akeneo\UserManagement\Bundle\Context\UserContext', // TODO: The current local should be given by $context (third parameter of normalize) instead of depending on UserContext
    ])->in('Akeneo\Channel\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
