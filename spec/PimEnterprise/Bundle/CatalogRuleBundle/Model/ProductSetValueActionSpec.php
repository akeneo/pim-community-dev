<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Model;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;

class ProductSetValueActionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            [
                'field' => 'sku',
                'value' => 'RATM-NIN-001',
                'locale' => 'fr_FR',
                'scope' => 'ecommerce',
                'type' => ProductSetValueActionInterface::ACTION_TYPE
            ]
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction');
    }

    function it_is_an_action()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Model\ActionInterface');
    }

    function it_is_a_product_set_value_action()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface');
    }

    function it_constructs_a_product_action()
    {
        $this->getField()->shouldReturn('sku');
        $this->getValue()->shouldReturn('RATM-NIN-001');
        $this->getLocale()->shouldReturn('fr_FR');
        $this->getScope()->shouldReturn('ecommerce');
    }
}
