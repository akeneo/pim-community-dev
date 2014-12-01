<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Model;

use PhpSpec\ObjectBehavior;

class ProductConditionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['field' => 'sku', 'operator' => 'EQUALS', 'value' => 'RATM-NIN-001']);
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
        $this->getValue()->shouldReturn('RATM-NIN-001');
    }

    public function it_throws_an_exception_when_trying_to_construct_a_condition_with_invalid_data()
    {
        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')
            ->during(
                '__construct',
                [
                    ['field' => new \stdClass(), 'operator' => [], 'value' => 'value']
                ]
            );
    }

    public function it_throws_an_exception_when_trying_to_construct_a_condition_with_missing_data()
    {
        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\MissingOptionsException')
            ->during('__construct', [['field' => 'field']]);
    }
}
