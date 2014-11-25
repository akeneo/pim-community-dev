<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Model;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction;
use Prophecy\Argument;

class ProductSetValueActionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                'field' => 'sku',
                'value' => 'RATM-NIN-001',
                'locale' => 'FR_fr',
                'scope' => 'ecommerce',
                'type' => ProductSetValueAction::TYPE
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction');
    }

    function it_is_an_action()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\ActionInterface');
    }

    function it_is_a_product_set_value_action()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface');
    }

    function it_constructs_a_product_action()
    {
        $this->getField()->shouldReturn('sku');
        $this->getValue()->shouldReturn('RATM-NIN-001');
        $this->getLocale()->shouldReturn('FR_fr');
        $this->getScope()->shouldReturn('ecommerce');
    }

    function it_throws_an_exception_when_trying_to_construct_an_action_with_invalid_data()
    {
        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')
            ->during(
                '__construct',
                [
                    [
                        'field' =>  new \stdClass(),
                        'value' => 'RATM-NIN-001',
                        'locale' => 'FR_fr',
                        'scope' => 'ecommerce',
                        'type' => ProductSetValueAction::TYPE
                    ]
                ]
            );
    }

    function it_throws_an_exception_when_trying_to_construct_an_action_with_missing_data()
    {
        $this->shouldThrow('\Symfony\Component\OptionsResolver\Exception\MissingOptionsException')
            ->during('__construct', [['field' => 'field']]);
    }
}
