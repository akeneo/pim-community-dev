<?php

namespace Akeneo\Tool\Bundle\FileStorageBundle\DependencyInjection\Compiler;

use Behat\Behat\HelperContainer\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetLazyRootCreationToLocalStorageAdapterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        try {
            $definition = $container->getDefinition('oneup_flysystem.local_storage_adapter_adapter');
            $lazyRootCreation = $container->getParameter('local_storage_lazy_root_creation') ?? false;
            $definition->addArgument($lazyRootCreation);
        } catch (ServiceNotFoundException) {
        }
    }
}
