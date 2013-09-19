<?php

namespace Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\LogicException;

class ProcessorRegistryCompilerPass implements CompilerPassInterface
{
    const PROCESSOR_REGISTRY_TAG = 'oro_importexport.processor_registry';
    const PROCESSOR_TAG          = 'oro_importexport.processor';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $processorsByTypes = $this->findProcessorTaggedServiceIdsByTypes($container);
        $registries = $this->findTaggedProcessorRegistryServiceIds($container);

        foreach ($registries as $registryId => $registryTags) {
            foreach ($registryTags as $registryTag) {
                $registry = $container->getDefinition($registryId);
                $this->addRegistryProcessors($registry, $registryTag, $processorsByTypes);
            }
        }
    }

    /**
     * @param Definition $registryDefinition
     * @param array $registryTag
     * @param array $processorsByTypes
     */
    protected function addRegistryProcessors(
        Definition $registryDefinition,
        array $registryTag,
        array $processorsByTypes
    ) {
        $type = $registryTag['type'];

        if (empty($processorsByTypes[$type])) {
            return;
        }

        foreach ($processorsByTypes[$type] as $processorId => $processorTags) {
            foreach ($processorTags as $processorTag) {
                $registryDefinition->addMethodCall(
                    'registerProcessor',
                    array(new Reference($processorId), $processorTag['entity'], $processorTag['alias'])
                );
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @return array
     */
    protected function findTaggedProcessorRegistryServiceIds(ContainerBuilder $container)
    {
        $registries = $container->findTaggedServiceIds(self::PROCESSOR_REGISTRY_TAG);

        foreach ($registries as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $this->assertTagHasAttributes($serviceId, self::PROCESSOR_REGISTRY_TAG, $tag, array('type'));
            }
        }

        return $registries;
    }

    /**
     * @param ContainerBuilder $container
     * @return array
     */
    protected function findProcessorTaggedServiceIdsByTypes(ContainerBuilder $container)
    {
        $result = array();

        $processors = $container->findTaggedServiceIds(self::PROCESSOR_TAG);

        foreach ($processors as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $this->assertTagHasAttributes(
                    $serviceId,
                    self::PROCESSOR_TAG,
                    $tag,
                    array('type', 'entity', 'alias')
                );

                $type = $tag['type'];

                if (!isset($result[$type])) {
                    $result[$type] = array();
                }
                if (!isset($result[$type][$serviceId])) {
                    $result[$type][$serviceId] = array();
                }

                $result[$type][$serviceId][] = $tag;
            }
        }

        return $result;
    }

    /**
     * @param string $serviceId
     * @param string $tagName
     * @param array $tagAttributes
     * @param array $requiredAttributes
     * @throws LogicException
     */
    private function assertTagHasAttributes($serviceId, $tagName, array $tagAttributes, array $requiredAttributes)
    {
        foreach ($requiredAttributes as $attribute) {
            if (empty($tagAttributes[$attribute])) {
                throw new LogicException(
                    sprintf('Tag "%s" for service "%s" must have attribute "%s"', $tagName, $serviceId, $attribute)
                );
            }
        }
    }
}
