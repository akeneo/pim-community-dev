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
        'Symfony\Bundle\FrameworkBundle',
        'Sensio\Bundle\FrameworkExtraBundle',
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component',

        // TODO: Inverse the dependency (should be a standalone JS component)
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',

        // TODO: The dependency to Platform is not normal, rework the Bundle
        'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',
        'Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface',
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\AbstractNotificationFactory',
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface',

        // TODO: the feature uses the datagrid
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',

        // TODO: remove all links by reference
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\UserManagement\Component\Model\GroupInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\UserManagement\Component\Model\Group',

        // TODO: relationship between bounded context (query data though repository)
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        // TODO: usage of public constant
        'Akeneo\Pim\Permission\Component\Attributes',

        // TODO: class to duplicate (used for notification)
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DatePresenter',

        // TODO: May be a Tool
        'Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor'
    ])->in('Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Gedmo\Sluggable\Util\Urlizer', // used to format the project identifier

        // TODO: relationship between bounded context (query data through repository)
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\LocaleAccessRepository',
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        // TODO: remove all links by reference
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',
        'Akeneo\Channel\Component\Model\CurrencyInterface',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\UserManagement\Component\Model\GroupInterface',
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',

        // TODO: the project should not be linked to a datagrid view, we should use project filter instead
        'Oro\Bundle\PimDataGridBundle\Entity\DatagridView',
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\DatagridViewTypes',

        // TODO: usage of public constant
        'Akeneo\Pim\Permission\Component\Attributes',

        // TODO: A component should not depend on a bundle
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job\RefreshProjectCompletenessJobLauncher',
        'Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface',

        // TODO: TWA should have its own exceptions.
        'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException',

        // TODO: pre processed data are computed thanks to value complete checker
        'Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface',
    ])->in('Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
