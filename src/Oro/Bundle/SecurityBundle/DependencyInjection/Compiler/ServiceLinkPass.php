<?php

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Oro\Bundle\SecurityBundle\DependencyInjection\Utils\ServiceLink;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ServiceLinkPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_service_link';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($tags as $id => $tag) {
            /** @var Definition $serviceLinkDef */
            $serviceLinkDef = $container->getDefinition($id);

            if (!isset($tag[0]['service'])) {
                throw new \RuntimeException(
                    sprintf("Tag '%s' for service '%s' doesn't have required param 'service'", self::TAG_NAME, $id)
                );
            }

            $serviceId = $tag[0]['service'];
            $isOptional = false;
            if (strpos($serviceId, '?') === 0) {
                $serviceId = substr($serviceId, 1);
                $isOptional = true;
            }

            if ($container->hasDefinition($serviceId)) {
                // the service we are referred to must be public
                $serviceDef = $container->getDefinition($serviceId);
                if (!$serviceDef->isPublic()) {
                    $serviceDef->setPublic(true);
                }
            } elseif (!$isOptional) {
                throw new \RuntimeException(
                    sprintf(
                        'Target service "%s" is undefined. The service link "%s" with tag "%s" and tag-service "%s"',
                        $serviceId,
                        $id,
                        self::TAG_NAME,
                        $serviceId
                    )
                );
            }

            $serviceLinkDef->setClass(ServiceLink::class);
            $serviceLinkDef->setArguments([new Reference('service_container'), $serviceId, $isOptional]);
        }
    }
}
