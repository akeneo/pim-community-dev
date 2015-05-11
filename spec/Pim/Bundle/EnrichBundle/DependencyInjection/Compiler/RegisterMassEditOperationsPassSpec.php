<?php

namespace spec\Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\DependencyInjection\Compiler\RegisterMassEditOperationsPass;
use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterMassEditOperationsPassSpec extends ObjectBehavior
{
    function let(ReferenceFactory $referenceFactory)
    {
        $this->beConstructedWith($referenceFactory);
    }

    function it_is_a_compiler_pass()
    {
        $this->shouldHaveType('Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface');
    }

    function it_register_mass_edit_operations_using_the_correct_registry(
        $referenceFactory,
        ContainerBuilder $container,
        Definition $operationRegistry,
        Reference $uglifyOperation,
        Reference $duplicateOperation
    ) {
        $container->getDefinition(RegisterMassEditOperationsPass::OPERATION_REGISTRY)->willReturn($operationRegistry);
        $container->findTaggedServiceIds(RegisterMassEditOperationsPass::OPERATION_TAG)->willReturn([
            'mass_edit_action.uglify' => [
                [
                    'name'     => 'uglify_products',
                    'alias'    => 'uglify',
                    'datagrid' => 'product-grid'
                ]
            ],
            'mass_edit_action.duplicate' => [
                [
                    'name'     => 'duplicate_families',
                    'alias'    => 'duplicate',
                    'datagrid' => 'family-grid',
                    'acl'      => 'pim_enrich_product_duplicate_family'
                ]
            ],
        ]);

        $referenceFactory->createReference('mass_edit_action.uglify')->willReturn($uglifyOperation);
        $referenceFactory->createReference('mass_edit_action.duplicate')->willReturn($duplicateOperation);

        $operationRegistry->addMethodCall('register', [
            $uglifyOperation,
            'uglify',
            null,
            'product-grid'
        ])->shouldBeCalled();

        $operationRegistry->addMethodCall('register', [
            $duplicateOperation,
            'duplicate',
            'pim_enrich_product_duplicate_family',
            'family-grid'
        ])->shouldBeCalled();

        $this->process($container);
    }
}
