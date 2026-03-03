<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();
$finder->notPath('tests');
$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Akeneo\Platform\Bundle\FrameworkBundle\Security\SecurityFacadeInterface',
        'Oro\Bundle\SecurityBundle\Exception\AccessDeniedException',

        // Rules from Akeneo\Channel\Bundle

        'Doctrine',
        'Symfony\Component',
        'Symfony\Contracts',
        'Akeneo\Tool',
        'Akeneo\Channel',
        'Twig',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // Rules from Akeneo\Channel\Component

        'Webmozart\Assert\Assert',
        'Symfony\Component',
        'Symfony\Contracts',
        'Doctrine\Common',
        'Doctrine\Persistence',
        'Akeneo\Tool\Component',

        // TIP-942: Channel should not be linked to Category
        'Akeneo\Category\Infrastructure\Component\Model\CategoryInterface',

        // TIP-1011: Create a Versioning component
        'Akeneo\Tool\Bundle\VersioningBundle\Repository\VersionRepositoryInterface',

        // TIP-939: Remove filter system for permissions
        'Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface',

        // TIP-1024: Drop UserContext
        'Akeneo\UserManagement\Bundle\Context\UserContext',

        // TIP-1017: Do not use public constants of AttributeTypes
        'Akeneo\Pim\Structure\Component\AttributeTypes',

        // TIP-1012: Create a Measure component
        'Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager',

        // PIM-10485: Get Category Tree without apply permission
        'Akeneo\Category\Api\CategoryTree',
        'Akeneo\Category\Api\FindCategoryTrees',

        'Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperInterface',
    ])->in('Akeneo\Channel\Infrastructure'),
];

$config = new Configuration($rules, $finder);

return $config;
