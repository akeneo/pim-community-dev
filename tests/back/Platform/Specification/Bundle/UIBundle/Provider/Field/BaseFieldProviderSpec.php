<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class BaseFieldProviderSpec extends ObjectBehavior
{
    function it_should_provide_the_field_for_the_given_attribute(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_catalog_boolean');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-switch-field');

        $attribute->getType()->willReturn('pim_catalog_date');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-datepicker-field');

        $attribute->getType()->willReturn('pim_catalog_file');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-media-uploader-field');

        $attribute->getType()->willReturn('pim_catalog_image');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-media-uploader-field');

        $attribute->getType()->willReturn('pim_catalog_metric');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-metric-field');

        $attribute->getType()->willReturn('pim_catalog_multiselect');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-multi-select-field');

        $attribute->getType()->willReturn('pim_catalog_number');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-number-field');

        $attribute->getType()->willReturn('pim_catalog_price_collection');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-price-collection-field');

        $attribute->getType()->willReturn('pim_catalog_simpleselect');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-simple-select-field');

        $attribute->getType()->willReturn('pim_catalog_identifier');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-text-field');

        $attribute->getType()->willReturn('pim_catalog_text');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-text-field');

        $attribute->getType()->willReturn('pim_catalog_textarea');
        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-textarea-field');
    }

    function it_should_not_provide_the_field_for_the_given_attribute_if_not_supported(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('not_supported_field');
        $this->supports($attribute)->shouldReturn(false);
    }
}
