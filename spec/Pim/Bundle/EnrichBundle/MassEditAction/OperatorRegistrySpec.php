<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionOperator;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditActionInterface;

class OperatorRegistrySpec extends ObjectBehavior
{
    function let(MassEditActionOperatorFactory $factory)
    {
        $this->beConstructedWith($factory);
    }

    function it_registers_one_operator_per_grid(
        MassEditActionInterface $massDeleteProduct,
        MassEditActionInterface $massDeleteFamily
    ) {
        $operators = [$productOperator, $familyOperator];
        #$factory->create()->will(function() use (&$operators) {
        #    return array_shift($operators);
        #});
        $factory->create()->willReturn($productOperator);
        $factory->create()->willReturn($familyOperator);
        $productOperator->registerMassEditAction('mass_delete_product', $massDeleteProduct, 'mass_delete_product_right')->shouldBeCalled();
        $familyOperator->registerMassEditAction('mass_delete_family', $massDeleteFamily, 'mass_delete_family_right')->shouldBeCalled();

        $this->register('product-grid', 'mass_delete_product', $massDeleteProduct, 'mass_delete_product_right');
        $this->register('family-grid', 'mass_delete_family', $massDeleteFamily, 'mass_delete_family_right');

        $this->getOperator('product-grid')->shouldReturn($productOperator);
        $this->getOperator('family')->shouldReturn($familyOperator);
    }

    function it_throws_exception_when_accessing_an_unknown_operator()
    {
        $exception = new \InvalidArgumentException('No operator is registered for datagrid "product"');
        $this->shouldThrow($exception)->duringGetOperator('product');
    }
}
