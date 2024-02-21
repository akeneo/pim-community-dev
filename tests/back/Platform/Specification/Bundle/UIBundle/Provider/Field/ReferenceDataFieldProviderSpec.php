<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class ReferenceDataFieldProviderSpec extends ObjectBehavior
{
    function it_should_support_and_provide_a_reference_data_field(AttributeInterface $attribute)
    {
        $attribute->getType()->willReturn('pim_reference_data_simpleselect');

        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-simple-select-reference-data-field');

        $attribute->getType()->willReturn('pim_reference_data_multiselect');

        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-multi-select-reference-data-field');
    }
}
