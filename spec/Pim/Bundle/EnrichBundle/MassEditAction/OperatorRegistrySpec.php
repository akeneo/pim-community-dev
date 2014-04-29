<?php

namespace spec\Pim\Bundle\EnrichBundle\MassEditAction;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator;

class OperatorRegistrySpec extends ObjectBehavior
{
    function it_registers_one_operator_per_grid(
        AbstractMassEditOperator $productOperator,
        AbstractMassEditOperator $familyOperator
    ) {
        $productOperator->getName()->willReturn('product');
        $familyOperator->getName()->willReturn('family');

        $this->register('product-grid', $productOperator);
        $this->register('family-grid', $familyOperator);

        $this->getOperator('product-grid')->shouldReturn($productOperator);
        $this->getOperator('family-grid')->shouldReturn($familyOperator);
    }

    function it_throws_exception_when_accessing_an_unknown_operator()
    {
        $exception = new \InvalidArgumentException('No operator is registered for datagrid "product"');
        $this->shouldThrow($exception)->duringGetOperator('product');
    }

    function it_throws_exception_when_registering_an_operator_with_the_same_alias(
        AbstractMassEditOperator $productOperator,
        AbstractMassEditOperator $anotherProductOperator
    ) {
        $exception = new \InvalidArgumentException('An operator with the alias "product-grid" is already registered');

        $this->register('product-grid', $productOperator);
        $this->shouldThrow($exception)->duringRegister('product-grid', $anotherProductOperator);
    }

    function it_throws_exception_when_registering_an_operator_with_an_already_attributed_name(
        AbstractMassEditOperator $productOperator,
        AbstractMassEditOperator $anotherProductOperator
    ) {
        $productOperator->getName()->willReturn('product');
        $anotherProductOperator->getName()->willReturn('product');

        $exception = new \LogicException('An operator with the name "product" is already registered');

        $this->register('product-grid', $productOperator);
        $this->shouldThrow($exception)->duringRegister('another-product-grid', $anotherProductOperator);
    }
}
