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
        'Webmozart\Assert\Assert',
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component',

        // TIP-1004: WidgetInterface located in Platform is used in multiple contexts
        // TIP-966: TWA should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',

        // TIP-967: TWA should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TIP-968: TWA depends on PIM/Enrichment
        // TIP-963: Define the Products public API
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface',

        // TIP-1013: Rework Notification system
        'Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface',
        'Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface',
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\AbstractNotificationFactory',
        'Akeneo\Platform\Bundle\NotificationBundle\Factory\NotificationFactoryInterface',

        // TODO: the feature uses the datagrid
        'Oro\Bundle\FilterBundle',
        'Oro\Bundle\DataGridBundle',
        'Oro\Bundle\PimDataGridBundle',

        // TIP-972: TWA should not be linked to User Group
        'Akeneo\UserManagement\Component\Model\Group',
        'Akeneo\UserManagement\Component\Model\GroupInterface',

        // TIP-973: TWA should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',
        'Akeneo\UserManagement\Component\Model\User',
        'Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException',

        // TIP-937: PIM/Enrichment should not be linked to Family
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',

        // TIP-1030: Public API for Structure
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',

        // TODO: Permission
        'Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes',

        // TIP-976: TWA should not be linked to Workflow
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DatePresenter',
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Datasource\ProductProposalDatasource',

        // TIP-977: Move CommandExecutor to Tool
        'Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor',

        // TIP-1023: CatalogContext should be dropped
        'Akeneo\Pim\Enrichment\Bundle\Context\CatalogContext',
    ])->in('Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle'),
    $builder->only([
        'Symfony\Component',
        'Symfony\Contracts',
        'Akeneo\Tool\Component',
        'Doctrine\Common',
        'Gedmo\Sluggable\Util\Urlizer', // used to format the project identifier

        // TIP-966: TWA should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',

        // TIP-967: TWA should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TIP-968: TWA depends on PIM/Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator', // ideally it should be either public api or we should duplicate this algorithm in this context

        // TIP-971 ProjectRemoverInterface should not be linked to PIM/Enrichment or PIM/Structure
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',
        'Akeneo\Pim\Structure\Component\Model\AttributeInterface',
        'Akeneo\Channel\Component\Model\CurrencyInterface',

        // TIP-972: TWA should not be linked to User Group
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-973: TWA should not be linked to User
        'Akeneo\UserManagement\Component\Model\GroupInterface',

        // TIP-937: PIM/Enrichment should not be linked to Family
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',

        // TIP-970: TWA should not be linked to Datagrid View
        'Oro\Bundle\PimDataGridBundle\Entity\DatagridView',
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\DatagridViewTypes',

        // TODO: permission
        'Akeneo\Pim\Permission\Component\Attributes',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\LocaleAccessRepository',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        // TIP-974: Move CursorableRepositoryInterface to component
        'Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface',

        // TIP-975: Use own exceptions
        'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException',
    ])->in('Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
