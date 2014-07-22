<?php

namespace Pim\Bundle\TransformBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;

/**
 * Compiler pass to register tagged encoders and normalizers into the pim serializer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplacePimSerializerArgumentsPass implements CompilerPassInterface
{
    /**
     * @staticvar integer The default priority for services
     */
    const DEFAULT_PRIORITY = 100;

    /**
     * @var ReferenceFactory
     */
    protected $factory;

    protected $serializerKey;

    protected $serializerTags;

    /**
     * @param ReferenceFactory|null $factory
     */
    public function __construct($serializerKey, array $serializerTags, ReferenceFactory $factory = null)
    {
        $this->serializerKey  = $serializerKey;
        $this->serializerTags = $serializerTags;
        $this->factory = $factory ?: new ReferenceFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->serializerKey)) {
            return;
        }

        $args = [];
        foreach ($this->serializerTags as $serializerTag) {
            $args[] = $this->getDependencyReferences($container, $serializerTag);
        }

        $container->getDefinition($this->serializerKey)->setArguments($args);
    }

    /**
     * Returns an array of service references for a specified tag name
     *
     * @param ContainerBuilder $container
     * @param string           $tagName
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function getDependencyReferences(ContainerBuilder $container, $tagName)
    {
        $priorities = array();
        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $priority = isset($attributes[0]['priority'])
                    ? $attributes[0]['priority']
                    : static::DEFAULT_PRIORITY;
            if (!isset($priorities[$priority])) {
                $priorities[$priority] = array();
            }
            $priorities[$priority][] = $this->factory->createReference($id);
        }

        krsort($priorities);
        $sortedReferences = array();
        foreach ($priorities as $references) {
            $sortedReferences = array_merge($sortedReferences, $references);
        }

        return $sortedReferences;
    }
}
