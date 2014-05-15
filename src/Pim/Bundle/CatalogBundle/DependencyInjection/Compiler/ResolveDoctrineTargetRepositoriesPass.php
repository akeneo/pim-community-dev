<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Resolves doctrine target repositories
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetRepositoriesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->resolveTargetRepositories($container);
    }

    protected function resolveTargetRepositories(ContainerBuilder $container)
    {
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_repository');
        foreach ($this->getParametersMapping($container) as $entityParam => $repositoryClass) {
            $entityClass = $container->getParameter($entityParam);
            $definition->addMethodCall(
                'addResolveTargetRepository',
                array(
                    $entityClass,
                    $repositoryClass
                )
            );
        }
    }

    /**
     * Returns the parameter mappings
     *
     * @return string[]
     */
    protected function getParametersMapping(ContainerBuilder $container)
    {
        return array(
            'pim_catalog.entity.attribute.class' => 'Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository'
        );
    }
}
