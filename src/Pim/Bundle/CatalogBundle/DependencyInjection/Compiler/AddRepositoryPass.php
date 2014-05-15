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
    const REPOSITORY_REGISTRY_SERVICE = 'pim_catalog.doctrine.repository.registry';

    const REPOSITORY_TAG = 'pim_catalog.repository';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $repositoryRegistry = $container->getDefinition(self::REPOSITORY_REGISTRY_SERVICE);
        $repositoryServices = $container->findTaggedServiceIds(self::REPOSITORY_TAG);

        foreach (array_keys($repositoryServices) as $serviceId) {
            $repositoryDef = $container->findDefinition($serviceId);
            $entityClass   = $this->resolveParameter($container, current($repositoryDef->getArguments()));

            $repositoryRegistry->addMethodCall('addRepository', array($entityClass, new Reference($serviceId)));
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
