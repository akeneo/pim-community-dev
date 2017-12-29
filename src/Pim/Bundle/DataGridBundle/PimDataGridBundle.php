<?php

namespace Pim\Bundle\DataGridBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Pim DataGrid Bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimDataGridBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\AddFilterTypesPass())
            ->addCompilerPass(new Compiler\AddAttributeTypesPass())
            ->addCompilerPass(new Compiler\AddSelectorsPass())
            ->addCompilerPass(new Compiler\AddSortersPass())
            ->addCompilerPass(new Compiler\AddMassActionHandlersPass())
            ->addCompilerPass(new Compiler\ConfigurationPass());

        $productMappings = [
            realpath(__DIR__ . '/Resources/config/doctrine') => 'Pim\Bundle\DataGridBundle\Entity'
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
