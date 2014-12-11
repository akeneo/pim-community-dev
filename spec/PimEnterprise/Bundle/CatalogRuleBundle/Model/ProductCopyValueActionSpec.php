<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Model;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;

class ProductCopyValueActionSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            [
                'type' => ProductCopyValueActionInterface::TYPE,
                'from_field' => 'sku',
                'to_field' => 'description',
                'from_locale' => 'FR_fr',
                'to_locale' => 'FR_ch',
                'from_scope' => 'ecommerce',
                'to_scope' => 'tablet',
            ]
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction');
    }

    public function it_is_an_action()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\ActionInterface');
    }

    public function it_is_a_product_copy_value_action()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface');
    }

    public function it_constructs_a_product_action()
    {
        $this->getFromField()->shouldReturn('sku');
        $this->getToField()->shouldReturn('description');
        $this->getFromLocale()->shouldReturn('FR_fr');
        $this->getToLocale()->shouldReturn('FR_ch');
        $this->getFromScope()->shouldReturn('ecommerce');
        $this->getToScope()->shouldReturn('tablet');
    }
}
