<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Resolves doctrine target repositories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetRepositoriesPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->resolveTargetRepositories($container);
    }

    /**
     * Resolve target repositories
     *
     * @param ContainerBuilder $container
     */
    protected function resolveTargetRepositories(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('pim_catalog.event_subscriber.resolve_target_repository');
        foreach ($this->getParametersMapping($container) as $repositoryClass => $objectClass) {
            $definition->addMethodCall(
                'addResolveTargetRepository',
                array(
                    $objectClass,
                    $repositoryClass
                )
            );
        }
    }

    /**
     * Returns the parameter mappings
     * array(
     *     'repositoryClass' => 'entityClass'
     * )
     *
     * @param ContainerBuilder $container
     *
     * @return string[]
     */
    protected function getParametersMapping(ContainerBuilder $container)
    {
        $repositoryIds = $container->findTaggedServiceIds('pim_repository');

        $mapping = array();
        foreach (array_keys($repositoryIds) as $repositoryId) {
            $repositoryDef   = $container->getDefinition($repositoryId);
            $repositoryClass = $this->resolveParameter($container, $repositoryDef->getClass());
            $entityClass     = $this->resolveParameter($container, current($repositoryDef->getArguments()));

            $mapping[$repositoryClass] = $entityClass;
        }

        return $mapping;
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
