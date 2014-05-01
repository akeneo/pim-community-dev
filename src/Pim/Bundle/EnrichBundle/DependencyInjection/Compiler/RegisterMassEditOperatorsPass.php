<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;

/**
 * Register mass edit operators
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterMassEditOperatorsPass implements CompilerPassInterface
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
        if (!$container->hasDefinition('pim_enrich.mass_edit_action.operator.registry')) {
            return;
        }

        $def = $container->getDefinition('pim_enrich.mass_edit_action.operator.registry');

        foreach ($container->findTaggedServiceIds('pim_enrich.mass_edit_action_operator') as $id => $config) {
            $def->addMethodCall('register', [$config[0]['datagrid'], $this->factory->createReference($id)]);
        }
    }
}
