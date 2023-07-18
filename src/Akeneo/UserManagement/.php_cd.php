<?php

use Akeneo\CouplingDetector\Configuration\Configuration;
use Akeneo\CouplingDetector\Configuration\DefaultFinder;
use Akeneo\CouplingDetector\RuleBuilder;

$finder = new DefaultFinder();

$builder = new RuleBuilder();

$rules = [
    $builder->only([
        'Symfony\Component',
        'Doctrine\Common',
        'Doctrine\Inflector',
        'Doctrine\Persistence',
        'Akeneo\Tool',
        'Webmozart\Assert\Assert',
        // TODO: The feature uses the datagrid
        'Oro\Bundle\PimDataGridBundle',

        // TIP-945: User Management should not depend on Channel and Enrichment
        'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

        // TIP-944: UserManager used in component
        'Akeneo\UserManagement\Bundle\Manager\UserManager',
        'Akeneo\UserManagement\Domain\Permissions',

        // TODO: This dependency should be removed, Bundle dependency
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
        'Oro\Bundle\SecurityBundle\Acl\AccessLevel',
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository',
        'Oro\Bundle\SecurityBundle\SecurityFacade',

        // TIP-947: UI Locale Provider should be part of UserManagement
        'Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider',

        // These files moved from Tool to Category bounded context
        // Usermanagement BC should query BC via exposed Commands
        // Ticket created : GRF-180
        'Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface'
    ])->in('Akeneo\UserManagement\Component'),
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Symfony\Contracts',
        'Akeneo\Tool',
        'Akeneo\UserManagement',
        'Webmozart\Assert\Assert',
        'Oro\Bundle\SecurityBundle',
        'Symfony\Bundle\FrameworkBundle',
        'Symfony\Bundle\SecurityBundle',
        'Twig\TwigFunction',
        'Oro\Bundle\DataGridBundle\Extension\Action\Actions\NavigateAction',
        'Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags',

        // TIP-1007: Clean VisibilityChecker system
        'Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker',

        // TIP-945: User Management should not depend on Channel and Enrichment
        'Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface',
        'Akeneo\Channel\Infrastructure\Component\Model\Channel',
        'Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface',
        'Akeneo\Channel\Infrastructure\Component\Model\Locale',
        'Akeneo\Channel\Infrastructure\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface',

        // TIP-1005: Clean UI form types
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType',

        'Oro\Bundle\UserBundle\Exception\UserCannotBeDeletedException',

        // TIP-1539: clean installer events
        'Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent',
        'Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents',

        // PLG-692: use email notification from Notification bundle
        'Akeneo\Platform\Bundle\NotificationBundle\Email\MailNotifierInterface',
        'Twig\Environment',
        'Twig\Extension\AbstractExtension',
        'Throwable',
        'Psr\Log\LoggerInterface',

        // These files moved from Tool to Category bounded context
        // Usermanagement BC should query BC via exposed Commands
        // Ticket created : GRF-180
        'Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface',
        'Akeneo\Category\Infrastructure\Component\Classification\Repository\CategoryRepositoryInterface'

    ])->in('Akeneo\UserManagement\Bundle'),
];

$config = new Configuration($rules, $finder);

return $config;
