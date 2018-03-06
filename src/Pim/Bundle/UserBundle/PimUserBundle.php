<?php

namespace Pim\Bundle\UserBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Pim\Bundle\UserBundle\DependencyInjection\Compiler\ResolveDoctrineTargetModelPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ResolveDoctrineTargetModelPass());

        $productMappings = [
            realpath(__DIR__ . '/Resources/config/model/doctrine/user') => 'Pim\Component\User\User',
            realpath(__DIR__ . '/Resources/config/model/doctrine/role') => 'Pim\Component\User\Role',
            realpath(__DIR__ . '/Resources/config/model/doctrine/group') => 'Pim\Component\User\Group',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                $productMappings,
                ['doctrine.orm.entity_manager'],
                false
            )
        );
    }
}
