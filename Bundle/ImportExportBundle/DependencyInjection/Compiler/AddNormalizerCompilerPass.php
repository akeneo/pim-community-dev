<?php

namespace Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AddNormalizerCompilerPass implements CompilerPassInterface
{
    const SERIALIZER_SERVICE = 'oro_importexport.serializer';
    const ATTRIBUTE_NORMALIZER_TAG = 'oro_importexport.normalizer';

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $normalizers = $this->findAndSortTaggedServices(self::ATTRIBUTE_NORMALIZER_TAG, $container);
        $container->getDefinition(self::SERIALIZER_SERVICE)->replaceArgument(0, $normalizers);
    }

    private function findAndSortTaggedServices($tagName, ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds($tagName);

        if (empty($services)) {
            throw new \RuntimeException(
                sprintf(
                    'You must tag at least one service as "%s" to use the import export Serializer service',
                    $tagName
                )
            );
        }

        $sortedServices = array();
        foreach ($services as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? $tag['priority'] : 0;
                $sortedServices[$priority][] = new Reference($serviceId);
            }
        }

        krsort($sortedServices);

        // Flatten the array
        return call_user_func_array('array_merge', $sortedServices);
    }
}
