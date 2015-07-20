<?php

namespace spec\Pim\Bundle\EnrichBundle\Provider\EmptyValue;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class BaseEmptyValueProviderSpec extends ObjectBehavior
{
    function it_should_provide_the_empty_value_for_the_given_attribute(AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $attribute->getDefaultMetricUnit()->willReturn('METER');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn([
            'data' => null,
            'unit' => 'METER'
        ]);

        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn([]);

        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn('');

        $attribute->getAttributeType()->willReturn('pim_catalog_boolean');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn(false);

        $attribute->getAttributeType()->willReturn('pim_catalog_price_collection');
        $this->supports($attribute)->shouldReturn(true);
        $this->getEmptyValue($attribute)->shouldReturn([]);
    }
}
