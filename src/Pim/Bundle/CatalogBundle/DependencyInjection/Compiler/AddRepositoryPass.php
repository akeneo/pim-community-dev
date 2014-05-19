<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Add repositories in a repository registry
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AddRepositoryPass implements CompilerPassInterface
{
    const REPOSITORY_REGISTRY_SERVICE = 'pim_catalog.doctrine.repository.factory';

    const REPOSITORY_TAG = 'pim_repository';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        //@todo: change for factory
        $repositoryRegistry = $container->getDefinition(self::REPOSITORY_REGISTRY_SERVICE);
        $repositoryServices = $container->findTaggedServiceIds(self::REPOSITORY_TAG);

        foreach (array_keys($repositoryServices) as $serviceId) {
            $repositoryDef = $container->getDefinition($serviceId);
            $entityClass   = $this->resolveParameter($container, current($repositoryDef->getArguments()));
            $methodCalls   = $repositoryDef->getMethodCalls();

            $repositoryRegistry->addMethodCall('addServiceId', array($entityClass, $methodCalls));
        }
    }

    /**
     * Resolve parameter definition
     *
     * @param ContainerBuilder $container
     * @param string           $parameter
     *
     * @return string
     */
    protected function resolveParameter(ContainerBuilder $container, $parameter)
    {
        return $container->getParameterBag()->resolveValue($parameter);
    }
}
