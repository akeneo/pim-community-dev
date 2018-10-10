<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Form\Transformer;

use PhpSpec\ObjectBehavior;

class StringToBooleanTransformerSpec extends ObjectBehavior
{
    function it_is_a_form_data_transformer()
    {
        $this->shouldImplement('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_transforms_strings_into_boolean()
    {
        $this->transform(false)->shouldReturn('0');
        $this->transform(true)->shouldReturn('1');
    }

    function it_reverse_transforms_strings_into_boolean()
    {
        $this->reverseTransform('1')->shouldReturn(true);
        $this->reverseTransform('0')->shouldReturn(false);
    }
}
