<?php

namespace AkeneoEnterprise\Test\IntegrationTestsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MakeServicesPublicForTestEnv implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (0 === strpos($id, 'pim') || 0 === strpos($id, 'akeneo') || 0 === strpos($id, 'oro')) {
                $definition->setPublic(true);
            }
        }
    }
}
