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

        // TODO: relationship between bounded context (query data though repository)
        // TODO: Maybe we don't need the whole attribute
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',

        // TODO: Rule engine sends notification
        'Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface',
        'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',

        // TODO: To remove
        'Akeneo\Pim\Enrichment\Bundle\Resolver\FQCNResolver',

        // TODO: Problem on product value presentation (new tool or something else?)
        // TODO: UserContext shouldn't be injected in any service it is a contextual parameter
        'Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter\PresenterRegistryInterface',
        'Akeneo\Platform\Bundle\UIBundle\Resolver\LocaleResolver',
    ])->in('Akeneo\Pim\Automation\RuleEngine\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',

        // TODO: Check how to decouple that from the enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators',
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Remover\RemoverRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Adder\AdderRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Copier\CopierRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Updater\Setter\SetterRegistryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface', // the engine creates a fake product to allow validation
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext', // used to version products when a rule is applied
        'Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager', // used to version products when a rule is applied

        // TODO: The rule engine sends notifications
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\AbstractNotificationFactory',
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface',

        // TODO: the component should not use a bundle => split the Tool\RuleEngine into Bundle + Component
        'Akeneo\Tool\Bundle\RuleEngineBundle',

        // TODO: called in ProductRuleSelector but not used => remove the dependency
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',

    ])->in('Akeneo\Pim\Automation\RuleEngine\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
