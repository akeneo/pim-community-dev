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
        'Akeneo\Pim\Automation\RuleEngine\Component',
        'Oro\Bundle\SecurityBundle\Annotation\AclAncestor',

        // TODO: the rule feature uses the datagrid
        'Oro\Bundle\PimDataGridBundle',
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\FilterBundle',

        // TIP-960: Rule Engine should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface',
        'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',

        // TIP-957: Do not use FQCN resolver
        'Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver',

        // TIP-1019: Move presenters
        'Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface',

        // TIP-1022: Drop LocaleResolver
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',
    ])->in('Akeneo\Pim\Automation\RuleEngine\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',

        // TIP-960: Rule Engine should not be linked to Attribute
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',

        // TIP-961: Remove dependency to ProductRepositoryInterface
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',

        // TIP-962: Rule Engine depends on PIM/Enrichment
        // TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface', // the engine creates a fake product to allow validation

        // TIP-1011: Create a Versioning component
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext', // used to version products when a rule is applied
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager', // used to version products when a rule is applied

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\AbstractNotificationFactory',
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface',

        // TIP-964: Split Tool/RuleEngine into component + bundle
        'Akeneo\Tool\Bundle\RuleEngineBundle',

    ])->in('Akeneo\Pim\Automation\RuleEngine\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
