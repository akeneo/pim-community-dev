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
        'Oro\Bundle\PimDataGridBundle\Entity\DatagridView', // TODO: Link by reference instead of id
        'Akeneo\UserManagement\Bundle\Manager\UserManager', // TODO: We use it to encode the user's password, Introduce interface for the update password
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager', // TODO: This dependency should be removed
        'Akeneo\Channel\Component\Model\LocaleInterface', // TODO: Link by reference instead of id
        'Akeneo\Channel\Component\Model\ChannelInterface', // TODO: Link by reference instead of id
    ])->in('Akeneo\UserManagement\Component'),
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\UserManagement\Component',
        'Oro\Bundle\SecurityBundle',
        'Symfony\Bundle\FrameworkBundle',
        'Akeneo\Channel\Component\Model\ChannelInterface', // TODO: The channel is linked by reference instead of id
        'Akeneo\Channel\Component\Model\Locale', // TODO: Use for entity form
        'Akeneo\Channel\Component\Model\LocaleInterface', // TODO: The locale is linked by reference instead of id
        'Akeneo\Channel\Component\Model\Channel', // TODO: Use for entity form
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType', // TODO: Duplicate this class where it is used and remove the original one
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\DateType', // TODO: Duplicate this class where it is used and remove the original one (is birthday really useful?)
        'Pim\Bundle\EnrichBundle\Form\Type\LightEntityType', // TODO: Duplicate this class where it is used and remove the original one
        'Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface', // TODO: Need real decoupling between UserManagement and Categories, maybe Enrichment should add the field "category default tree" in the form
        'Pim\Bundle\EnrichBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker', // TODO: Front issue
        'Sensio\Bundle\FrameworkExtraBundle', // TODO: Remove the usage of @Template method (quick) Not priority one
    ])->in('Akeneo\UserManagement\Bundle'),
];

$config = new Configuration($rules, $finder);

return $config;
