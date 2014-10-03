<?php

namespace Pim\Bundle\DashboardBundle\DependencyInjection\Compiler;

use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register widget into registry compiler pass
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
        if (!$container->hasDefinition('pim_dashboard.widget.registry')) {
            return;
        }
        $definition = $container->getDefinition('pim_dashboard.widget.registry');

        foreach ($container->findTaggedServiceIds('pim_dashboard.widget') as $serviceId => $tag) {
            $position = isset($tag[0]['position']) ? $tag[0]['position'] : 0;
            $definition->addMethodCall('add', array($this->factory->createReference($serviceId), $position));
        }
    }
}
