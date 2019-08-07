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
        'Akeneo\Tool',
        // TODO: The feature uses the datagrid
        'Oro\Bundle\PimDataGridBundle',

        // TIP-945: User Management should not depend on Channel and Enrichment
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TIP-944: UserManager used in component
        'Akeneo\UserManagement\Bundle\Manager\UserManager',

        // TODO: This dependency should be removed, Bundle dependency
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
        'Oro\Bundle\SecurityBundle\SecurityFacade',

        // TIP-947: UI Locale Provider should be part of UserManagement
        'Akeneo\Platform\Bundle\UIBundle\UiLocaleProvider'
    ])->in('Akeneo\UserManagement\Component'),
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\UserManagement',
        'Oro\Bundle\SecurityBundle',
        'Sensio\Bundle\FrameworkExtraBundle',
        'Symfony\Bundle\FrameworkBundle',
        'FOS\OAuthServerBundle\Entity\ClientManager', // used by API client controller
        'OAuth2\OAuth2', // used by API client controller
        'Swift_Mailer',

        // TIP-1007: Clean VisibilityChecker system
        'Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker',

        // TIP-945: User Management should not depend on Channel and Enrichment
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\Channel',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\Locale',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        // TIP-1005: Clean UI form types
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType',
    ])->in('Akeneo\UserManagement\Bundle'),
];

$config = new Configuration($rules, $finder);

return $config;
