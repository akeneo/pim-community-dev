<?php

namespace Pim\Bundle\VersioningBundle\DependencyInjection\Compiler;

use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterVersioningSerializerPass implements CompilerPassInterface
{
    /**
     * @staticvar integer The default priority for services
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
        if (!$container->hasDefinition('pim_versioning.serializer')) {
            return;
        }

        $container->getDefinition('pim_versioning.serializer')->setArguments(
            [
                $this->getDependencyReferences($container, 'pim_versioning.normalizer')
            ]
        );
    }

    protected function getDependencyReferences($container, $tagName)
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
