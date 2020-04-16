<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation;
use PhpSpec\ObjectBehavior;

class OperationSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedThrough('fromNormalized', [['operator' => Operation::MULTIPLY, 'field' => 'length']]);
        $this->shouldHaveType(Operation::class);

        $this->getOperator()->shouldBe(Operation::MULTIPLY);
        $this->getOperand()->shouldBeLike(Operand::fromNormalized(['field' => 'length']));
    }

    function it_cannot_be_instantiated_without_an_operator()
    {
        $this->beConstructedThrough('fromNormalized', [['field' => 'length']]);
        $this->shouldThrow(new \InvalidArgumentException('Operation expects an "operator" key'))
            ->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_an_invalid_operator()
    {
        $this->beConstructedThrough('fromNormalized', [['operator' => 'my_custom_operator', 'field' => 'length']]);
        $this->shouldThrow(new \InvalidArgumentException('Operation expects one of the following operators: multiply, add, divide, subtract'))
             ->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_an_invalid_operand()
    {
        $this->beConstructedThrough('fromNormalized', [['operator' => Operation::ADD, 'toto' => 42]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_represent_a_division_by_zero()
    {
        $this->beConstructedThrough('fromNormalized', [['operator' => Operation::DIVIDE, 'value' => 0]]);
        $this->shouldThrow(new \InvalidArgumentException('Cannot accept a division by zero operation'))
            ->duringInstantiation();
    }
}
