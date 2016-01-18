<?php

namespace spec\Pim\Bundle\EnrichBundle\Provider\Field;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class WysiwygFieldProviderSpec extends ObjectBehavior
{
    function it_should_support_and_provide_a_wysiwyg_field(AttributeInterface $attribute)
    {
        $attribute->isWysiwygEnabled()->willReturn(true);

        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-wysiwyg-field');
    }
}
