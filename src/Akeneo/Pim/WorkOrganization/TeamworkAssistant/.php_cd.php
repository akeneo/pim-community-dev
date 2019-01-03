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

        // TIP-966: TWA should not be linked to Locale
        'Akeneo\Channel\Component\Model\LocaleInterface',

        // TIP-967: TWA should not be linked to Channel
        'Akeneo\Channel\Component\Model\ChannelInterface',

        // TIP-968: TWA depends on PIM/Enrichment
        'Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface',
        'Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface',

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

        // TIP-972: TWA should not be linked to User Group
        'Akeneo\UserManagement\Component\Model\Group',
        'Akeneo\UserManagement\Component\Model\GroupInterface',

        // TIP-973: TWA should not be linked to User
        'Akeneo\UserManagement\Component\Model\UserInterface',

        // TIP-937: PIM/Enrichment should not be linked to Family
        'Akeneo\Pim\Structure\Component\Model\FamilyInterface',

        // TODO: relationship between bounded context (query data though repository)
        'Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        // TODO: usage of public constant
        'Akeneo\Pim\Permission\Component\Attributes',

        // TIP-976: TWA should not be linked to Workflow
        'Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\DatePresenter',

        // TIP-977: Move CommandExecutor to Tool
        'Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor'
    ])->in('Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle'),
    $builder->only([
        'Symfony\Component',
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
        'Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface',

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

        // TODO: relationship between bounded context (query data through repository)
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\LocaleAccessRepository',
        'Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository',

        // TIP-970: TWA should not be linked to Datagrid View
        'Oro\Bundle\PimDataGridBundle\Entity\DatagridView',
        'Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\DatagridViewTypes',

        // TODO: usage of public constant
        'Akeneo\Pim\Permission\Component\Attributes',

        // TIP-974: Move CursorableRepositoryInterface to component
        'Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\CursorableRepositoryInterface',

        // TIP-975: Use own exceptions
        'Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException',
    ])->in('Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component'),
];

$config = new Configuration($rules, $finder);

return $config;
