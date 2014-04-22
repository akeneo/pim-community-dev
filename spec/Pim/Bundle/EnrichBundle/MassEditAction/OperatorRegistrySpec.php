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
}
