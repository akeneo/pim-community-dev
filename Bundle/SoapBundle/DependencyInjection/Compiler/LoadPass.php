<?php

namespace Oro\Bundle\SoapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class LoadPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $classes = [
            'Oro\Bundle\SearchBundle\Controller\Api\SoapController',
            'Oro\Bundle\UserBundle\Controller\Api\Soap\UserController',
            'Oro\Bundle\UserBundle\Controller\Api\Soap\RoleController',
            'Oro\Bundle\UserBundle\Controller\Api\Soap\GroupController',
            'Oro\Bundle\AddressBundle\Controller\Api\Soap\AddressController',
            'Oro\Bundle\AddressBundle\Controller\Api\Soap\AddressTypeController',
            'Oro\Bundle\AddressBundle\Controller\Api\Soap\CountryController',
            'Oro\Bundle\AddressBundle\Controller\Api\Soap\RegionController',
            'Oro\Bundle\DataAuditBundle\Controller\Api\Soap\AuditController',
            'Oro\Bundle\OrganizationBundle\Controller\Api\Soap\BusinessUnitController',
        ];

        $container
            ->getDefinition('oro_soap.loader')
            ->addArgument($classes);
    }
}
