<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Provider\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class WysiwygFieldProviderSpec extends ObjectBehavior
{
    function it_should_support_and_provide_a_wysiwyg_field(AttributeInterface $attribute)
    {
        $attribute->isWysiwygEnabled()->willReturn(true);

        $this->supports($attribute)->shouldReturn(true);
        $this->getField($attribute)->shouldReturn('akeneo-wysiwyg-field');
    }
}
