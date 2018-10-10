<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductConditionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ConditionInterface;
use PhpSpec\ObjectBehavior;

class ProductConditionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            ['field' => 'sku', 'operator' => 'EQUALS', 'value' => 'RATM-001', 'locale' => 'fr_FR', 'scope' => 'print']
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductCondition::class);
    }

    function it_is_a_condidtion()
    {
        $this->shouldHaveType(ConditionInterface::class);
    }

    function it_is_a_product_condidtion()
    {
        $this->shouldHaveType(ProductConditionInterface::class);
    }

    function it_constructs_a_product_condition()
    {
        $this->getField()->shouldReturn('sku');
        $this->getOperator()->shouldReturn('EQUALS');
        $this->getValue()->shouldReturn('RATM-001');
        $this->getLocale()->shouldReturn('fr_FR');
        $this->getScope()->shouldReturn('print');
    }
}
