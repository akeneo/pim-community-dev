<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory;

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
     * @staticvar int The default priority for services
     */
    const DEFAULT_PRIORITY = 100;

    /**
     * @var ReferenceFactory
     */
    protected $factory;

    /**
     * @param ReferenceFactory|null $factory
     */
    public function __construct(ReferenceFactory $factory = null)
    {
        $this->factory = $factory ?: new ReferenceFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_serializer')) {
            return;
        }

        $container->getDefinition('pim_serializer')->setArguments(
            array(
                $this->getDependencyReferences($container, 'pim_serializer.normalizer', static::DEFAULT_PRIORITY),
                $this->getDependencyReferences($container, 'pim_serializer.encoder', static::DEFAULT_PRIORITY)
            )
        );
    }

    /**
     * Returns an array of service references for a specified tag name
     *
     * @param ContainerBuilder $container
     * @param string           $tagName
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function getDependencyReferences(ContainerBuilder $container, $tagName, $priority)
    {
        $services = new \SplPriorityQueue();
        foreach ($container->findTaggedServiceIds($tagName) as $id => $attributes) {
            $services->insert(
                $this->factory->createReference($id),
                isset($attributes[0]['priority']) ? $attributes[0]['priority'] : $priority
            );
        }

        return array_values(iterator_to_array($services));
    }
}
