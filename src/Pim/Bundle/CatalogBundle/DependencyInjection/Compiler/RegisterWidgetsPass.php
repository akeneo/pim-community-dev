<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Reference\ReferenceFactory;

class RegisterWidgetsPass implements CompilerPassInterface
{
    /** @var ReferenceFactory */
    protected $factory;

    /**
     * @param ReferenceFactory $factory
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
        if (!$container->hasDefinition('pim_catalog.widget.registry')) {
            return;
        }
        $definition = $container->getDefinition('pim_catalog.widget.registry');

        foreach ($container->findTaggedServiceIds('pim_catalog.widget') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias']) ? $tag[0]['alias'] : $serviceId;
            $definition->addMethodCall('add', array($alias, $this->factory->createReference($serviceId)));
        }
    }
}
