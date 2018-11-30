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

        // TODO: The user as a default locale and default channel
        // TODO: A representation of the user should belong elsewhere where it is really useful to have those information
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',

        // TODO: We use it to encode the user's password, Bundle dependency
        'Akeneo\UserManagement\Bundle\Manager\UserManager',

        // TODO: This dependency should be removed, Bundle dependency
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager',
        'Oro\Bundle\SecurityBundle\SecurityFacade',

        // TODO: We should not depend on Platform
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

        // TODO: We should not depend on Platform
        'Akeneo\Platform\Bundle\UIBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker',

        // TODO: The user as a default locale and default channel and default category tree
        // TODO: A representation of the user should belong elsewhere where it is really useful to have those information
        'Akeneo\Channel\Component\Model\ChannelInterface',
        'Akeneo\Channel\Component\Model\Channel',
        'Akeneo\Channel\Component\Model\LocaleInterface',
        'Akeneo\Channel\Component\Model\Locale',
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface',

        // TODO: it should be moved somewhere we could centralize old symfony form
        // TODO: Used by Role form
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\EntityIdentifierType',
    ])->in('Akeneo\UserManagement\Bundle'),
];

$config = new Configuration($rules, $finder);

return $config;
