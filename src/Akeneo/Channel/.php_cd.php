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
        'Twig_Environment',
        // TODO: we should remove this dependencies, related to permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        'Akeneo\Platform\Bundle\UIBundle\Flash\Message', // TODO: fix it
        // TODO: The current local should be given by $context (third parameter of normalize) instead of depending on UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',
    ])->in('Akeneo\Channel\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Akeneo\Tool\Component',
        // TODO: Review the functionnal as it a real problem
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',
        // TODO: The Repository should be moved to Akeneo\Tool\Component
        'Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface',
        // TODO: we should remove this dependency, related to permissions for locale, it is an enrichment purpose
        // TODO: Used in Akeneo\Channel\Component\Normalizer\*\ChannelNormalizer check exactly where is it used
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',
        // TODO: The current locale should be given by $context (third parameter of normalize) instead of depending on UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',
        // TODO: Channels are currently tied to PIM_CATALOG_METRIC, to be able to convert units
        // TODO: Functionnal problem
        'Akeneo\Pim\Structure\Component\AttributeTypes',
        // TODO: Channels are currently tied to PIM_CATALOG_METRIC, to be able to convert units,
        // TODO: Create an interface in Channel/Component and an implementation in Channel/Bundle which uses it as it is allowed
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager',
    ])->in('Akeneo\Channel\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
