<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\FieldImpactActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operand;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\Operation;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\OperationList;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateAction;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCalculateActionInterface;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductTarget;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;
use PhpSpec\ObjectBehavior;

class ProductCalculateActionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'destination' => [
                'field' => 'volume',
            ],
            'source' => [
                'field' => 'width',
            ],
            'operation_list' => [
                [
                    'operator' => Operation::MULTIPLY,
                    'field' => 'length',
                ],
                [
                    'operator' => Operation::MULTIPLY,
                    'field' => 'height',
                ],
            ]
        ]);
    }

    function it_is_a_product_calculate_action()
    {
        $this->shouldHaveType(ProductCalculateAction::class);
        $this->shouldImplement(ActionInterface::class);
        $this->shouldImplement(ProductCalculateActionInterface::class);
    }

    function it_holds_a_destination()
    {
        $this->getDestination()->shouldBeLike(ProductTarget::fromNormalized(['field' => 'volume']));
    }

    function it_holds_a_source()
    {
        $this->getSource()->shouldBeLike(Operand::fromNormalized(['field' => 'width']));
    }

    function it_holds_a_list_of_operations()
    {
        $this->getOperationList()->shouldHaveType(OperationList::class);
    }

    function it_is_a_field_impact_action()
    {
        $this->shouldBeAnInstanceOf(FieldImpactActionInterface::class);
        $this->getImpactedFields()->shouldReturn(['volume']);
    }

    function it_holds_the_non_round_information()
    {
        $this->isRoundEnabled()->shouldBe(false);
        $this->getRoundPrecision()->shouldBeNull();
    }

    function it_holds_the_round_information()
    {
        $this->beConstructedWith([
            'destination' => [
                'field' => 'volume',
            ],
            'source' => [
                'field' => 'width',
            ],
            'operation_list' => [
                [
                    'operator' => Operation::MULTIPLY,
                    'field' => 'length',
                ],
                [
                    'operator' => Operation::MULTIPLY,
                    'field' => 'height',
                ],
            ],
            'round_precision' => 0,
        ]);
        $this->isRoundEnabled()->shouldBe(true);
        $this->getRoundPrecision()->shouldBe(0);
    }
}
