<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register batch operations into the batch operator
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterBatchOperationsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_catalog.batch_operation.operator')) {
            return;
        }

        $def = $container->getDefinition('pim_catalog.batch_operation.operator');

        foreach ($container->findTaggedServiceIds('pim_catalog.batch_operation') as $id => $config) {
            $def->addMethodCall('registerBatchOperation', array($config[0]['alias'], new Reference($id)));
        }
    }
}
