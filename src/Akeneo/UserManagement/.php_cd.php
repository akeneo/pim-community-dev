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
        'Akeneo\UserManagement\Component',
        'Oro\Bundle\PimDataGridBundle\Entity\DatagridView', // TODO: The locale is linked by reference instead of id
        'Akeneo\UserManagement\Bundle\Manager\UserManager', // TODO: We use it to encode the user's password
        'Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager', // TODO: This dependency should be removed
        'Akeneo\Channel\Component\Model\LocaleInterface', // TODO: The locale is linked by reference instead of id
        'Akeneo\Channel\Component\Model\ChannelInterface', // TODO: The channel is linked by reference instead of id
        'Akeneo\Tool\Component\Connector\ArrayConverter', // TODO: Remove that ligne when connector bundle/component will be moved in Tool
    ])->in('Akeneo\UserManagement\Component'),
    $builder->only([
        'Doctrine',
        'Symfony\Component',
        'Akeneo\Tool',
        'Akeneo\UserManagement',
        'Oro\Bundle\SecurityBundle',
        'Akeneo\Channel\Component\Model\ChannelInterface', // TODO: The channel is linked by reference instead of id
        'Akeneo\Channel\Component\Model\Channel', // TODO: Use for entity form
        'Akeneo\Channel\Component\Model\LocaleInterface', // TODO: The locale is linked by reference instead of id
        'Akeneo\Channel\Component\Model\Locale', // TODO: Use for entity form
        'Akeneo\Channel\Component\Repository\ChannelRepositoryInterface',
        'Akeneo\Channel\Component\Repository\LocaleRepositoryInterface',
        'Pim\Bundle\EnrichBundle\Form\Type\EntityIdentifierType', // TODO: it should be moved somewhere we could centralize old symfony form
        'Akeneo\Platform\Bundle\UIBundle\Form\Type\DateType', // TODO: it should be moved somewhere we could centralize old symfony form
        'Pim\Bundle\EnrichBundle\Form\Type\LightEntityType', // TODO: it should be moved somewhere we could centralize old symfony form
        'Pim\Component\Enrich\Provider\TranslatedLabelsProviderInterface', // TODO: it should be moved somewhere we could centralize old symfony form
        'Pim\Bundle\EnrichBundle\ViewElement\Checker\NonEmptyPropertyVisibilityChecker', // TODO: it should be moved somewhere we could centralize old symfony form or remove it
        'Sensio\Bundle\FrameworkExtraBundle', // TODO:Some old Oro controllers use Template annotation
        'Symfony\Bundle\FrameworkBundle', // TODO:Some old Oro controllers extend the symfony controller
    ])->in('Akeneo\UserManagement\Bundle'),
];

$config = new Configuration($rules, $finder);

return $config;
