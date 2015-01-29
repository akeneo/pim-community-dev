<?php

namespace Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\Resolver;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author    Langlade Arnaud <arn0d.dev@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractDoctrineTargetResolver
{
    /**
     * @param ContainerBuilder $container
     * @param array            $interfaces
     */
    public function resolve(ContainerBuilder $container, array $interfaces)
    {
        if (!$container->hasDefinition($this->getResolverDefinitionKey())) {
            return;
        }

        $definition = $container->findDefinition($this->getResolverDefinitionKey());

        foreach ($interfaces as $interface => $parameterName) {
            $definition->addMethodCall(
                $this->getResolverMethod(),
                array(
                    $this->getInterface($interface),
                    $this->getModel($container, $parameterName),
                    array()
                )
            );
        }
    }

    /**
     * @return string
     */
    abstract protected function getResolverDefinitionKey();

    /**
     * @return string
     */
    abstract protected function getResolverMethod();

    /**
     * @param ContainerBuilder $container
     * @param string           $key
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function getModel(ContainerBuilder $container, $key)
    {
        if ($container->hasParameter($key)) {
            return $container->getParameter($key);
        }

        if (class_exists($key)) {
            return $key;
        }

        throw new \InvalidArgumentException(
            sprintf('The class %s does not exists.', $key)
        );
    }

    /**
     * @param string $interface
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function getInterface($interface)
    {
        if (!interface_exists($interface)) {
            return $interface;
        }

        throw new \InvalidArgumentException(
            sprintf('The interface %s does not exists.', $interface)
        );
    }
}
