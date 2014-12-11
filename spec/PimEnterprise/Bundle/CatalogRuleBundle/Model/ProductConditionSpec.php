<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Model;

use PhpSpec\ObjectBehavior;

class ProductConditionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            ['field' => 'sku', 'operator' => 'EQUALS', 'value' => 'RATM-001', 'locale' => 'fr_FR', 'scope' => 'print']
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition');
    }

    public function it_is_a_condidtion()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\ConditionInterface');
    }

    public function it_is_a_product_condidtion()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface');
    }

    public function it_constructs_a_product_condition()
    {
        $this->getField()->shouldReturn('sku');
        $this->getOperator()->shouldReturn('EQUALS');
        $this->getValue()->shouldReturn('RATM-001');
        $this->getLocale()->shouldReturn('fr_FR');
        $this->getScope()->shouldReturn('print');
    }
}
