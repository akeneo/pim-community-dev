<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\DependencyInjection\Compiler;

use Akeneo\Pim\Permission\Bundle\Datagrid\Filter\PermissionFilter;
use Akeneo\Pim\Permission\Bundle\DependencyInjection\Compiler\AddPermissionFilterPass;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class AddPermissionFilterPassSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AddPermissionFilterPass::class);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldImplement(CompilerPassInterface::class);
    }

    function it_adds_permissions_filter(
        ContainerBuilder $container,
        Definition $configProdiverDefinition
    ) {
        $container->getDefinition('oro_datagrid.configuration.provider')->willReturn($configProdiverDefinition);
        $configProdiverDefinition->getArgument(0)->willReturn(
            [
                'product-grid' => [
                    'filters' => [
                        'columns' => [
                            'field' => [],
                        ],
                    ],
                    'fields' => [],
                ],
            ]
        );

        $configProdiverDefinition->replaceArgument(
            0,
            [
                'product-grid' => [
                    'filters' => [
                        'columns' => [
                            'field' => [],
                            'permissions' => [
                                'type' => 'product_permission',
                                'ftype' => 'choice',
                                'data_name' => 'permissions',
                                'label' => 'Permissions',
                                'options' => [
                                    'field_options' => [
                                        'multiple' => false,
                                        'choices' => [
                                            'pimee_workflow.product.permission.own' => PermissionFilter::OWN,
                                            'pimee_workflow.product.permission.edit' => PermissionFilter::EDIT,
                                            'pimee_workflow.product.permission.view' => PermissionFilter::VIEW,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'fields' => [],
                ],
            ]
        )->shouldBeCalled();

        $this->process($container)->shouldReturn(null);
    }
}
