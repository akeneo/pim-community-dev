<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\EmptyValue;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class BaseEmptyValueProviderSpec extends ObjectBehavior
{
    function it_should_provide_the_empty_value_for_the_given_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_metric');
        $attribute->getDefaultMetricUnit()->willReturn('METER');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn([
            'amount' => null,
            'unit' => 'METER'
        ]);

        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn([]);

        $attribute->getType()->willReturn('pim_catalog_text');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn('');

        $attribute->getType()->willReturn('pim_catalog_boolean');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn(false);

        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn([]);
    }
}
