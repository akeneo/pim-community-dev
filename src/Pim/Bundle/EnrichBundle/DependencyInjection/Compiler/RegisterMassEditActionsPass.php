<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Pim\Bundle\TransformBundle\DependencyInjection\Reference\ReferenceFactory;

/**
 * Register batch operations into the batch operator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterMassEditActionsPass implements CompilerPassInterface
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
        foreach ($container->findTaggedServiceIds('pim_enrich.mass_edit_action') as $id => $config) {

            // Mass Edit Action was originally used by the product grid
            // so, in order not to break BC, we fallback operator to the product one.
            // We may deprecate this behaviour in the future and enforce operator parameter
            // inside the tag.
            $operatorId = isset($config[0]['operator']) ? $config[0]['operator'] : 'pim_enrich.mass_edit_action.operator.product';
            $operatorDef = $container->getDefinition($operatorId);

            $operatorDef->addMethodCall(
                'registerMassEditAction',
                [
                    $config[0]['alias'],
                    $this->factory->createReference($id),
                    isset($config[0]['acl']) ? $config[0]['acl'] : null
                ]
            );
        }
    }
}
