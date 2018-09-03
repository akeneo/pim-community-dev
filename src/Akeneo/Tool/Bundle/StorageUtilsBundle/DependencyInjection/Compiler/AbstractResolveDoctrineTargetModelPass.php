<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Parameter;

/**
 * Resolves doctrine ORM Target models
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractResolveDoctrineTargetModelPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->resolveTargetEntities($container);
    }

    /**
     * Resolve target entity interfaces by using container parameters
     *
     * @param ContainerBuilder $container
     */
    protected function resolveTargetEntities(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        foreach ($this->getParametersMapping() as $interface => $parameterName) {
            $definition->addMethodCall(
                'addResolveTargetEntity',
                [
                    $interface,
                    new Parameter($parameterName),
                    []
                ]
            );
        }
    }

    /**
     * Returns the parameter mappings
     *
     * @return array
     */
    abstract protected function getParametersMapping();
}
