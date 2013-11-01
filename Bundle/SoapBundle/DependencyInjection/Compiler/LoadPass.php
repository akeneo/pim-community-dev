<?php

namespace Oro\Bundle\SoapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\Yaml\Yaml;

class LoadPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $classes = [];

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);

            if (file_exists($file = dirname($reflection->getFilename()) . '/Resources/config/oro/soap.yml')) {
                $classes = array_merge($classes, Yaml::parse(realpath($file))['classes']);
            }
        }

        $container
            ->getDefinition('oro_soap.loader')
            ->addArgument(array_unique($classes));
    }
}
