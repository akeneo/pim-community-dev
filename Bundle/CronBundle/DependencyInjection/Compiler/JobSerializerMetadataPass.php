<?php

namespace Oro\Bundle\CronBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class JobSerializerMetadataPass implements CompilerPassInterface
{
    const LOCATOR_SERVICE = 'jms_serializer.metadata.file_locator';
    const NAMESPACE_PREFIX = 'JMS\JobQueueBundle';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LOCATOR_SERVICE)) {
            return;
        }

        // replace
        //      'JMS\JobQueueBundle' => 'JMS\JobQueueBundle/Resources/config/serializer'
        // with
        //      'JMS\JobQueueBundle' => 'OroCronBundle/Resources/config/serializer/JobQueueBundle'
        $locatorDef = $container->getDefinition(self::LOCATOR_SERVICE);
        $directories = $locatorDef->getArgument(0);
        if (isset($directories[self::NAMESPACE_PREFIX])) {
            $bundles = $container->getParameter('kernel.bundles');
            $ref = new \ReflectionClass($bundles['OroCronBundle']);
            $directories[self::NAMESPACE_PREFIX] =
                dirname($ref->getFileName()) . '/Resources/config/serializer/JobQueueBundle';
            $locatorDef->replaceArgument(0, $directories);
        }
    }
}
