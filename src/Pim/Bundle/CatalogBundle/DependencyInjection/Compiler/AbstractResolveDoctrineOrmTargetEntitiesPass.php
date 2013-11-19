<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class AbstractResolveDoctrineOrmTargetEntitiesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        foreach ($this->getParametersMapping() as $interface => $parameterName) {
            $definition->addMethodCall(
                'addResolveTargetEntity',
                array(
                    $interface,
                    new Parameter($parameterName),
                    array()
                )
            );
        }
    }

    /**
     * Returns the parameter mappings
     *
     * @return array
     * @abstract
     */
    abstract protected function getParametersMapping();
}
