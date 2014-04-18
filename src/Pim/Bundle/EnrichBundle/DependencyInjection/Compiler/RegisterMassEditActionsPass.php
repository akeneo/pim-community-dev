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

    /** @var array */
    protected $operators = [];

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
            // so, in order not to break BC, we fallback datagrid on product-grid.
            // We may deprecate this behaviour in the future and enforce datagrid parameter
            // inside the tag.
            $datagrid = isset($config[0]['datagrid']) ? $config[0]['datagrid'] : 'product-grid';
            $operatorDef = $this->getOperatorDefinition($container, $datagrid);

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

    protected function getOperatorDefinition(ContainerBuilder $container, $datagrid)
    {
        if (!isset($this->operators[$datagrid])) {
            foreach ($container->findTaggedServiceIds('pim_enrich.mass_edit_action_operator') as $id => $config) {
                if ($config[0]['datagrid'] === $datagrid) {
                    $this->operators[$datagrid] = $container->getDefinition($id);
                }
            }
        }

        if (!isset($this->operators[$datagrid])) {
            throw new \LogicException(
                sprintf(
                    'Cannot find any mass edit action operator for datagrid "%s"', $datagrid
                )
            );
        }

        return $this->operators[$datagrid];
    }
}
