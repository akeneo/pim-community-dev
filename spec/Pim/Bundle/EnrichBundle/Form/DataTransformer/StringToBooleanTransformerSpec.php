<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\DataTransformer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringToBooleanTransformerSpec extends ObjectBehavior
{
    function it_is_a_form_data_transformer()
    {
        $this->shouldImplement('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_transforms_strings_into_boolean()
    {
        $this->transform('0')->shouldReturn(false);
        $this->transform('1')->shouldReturn(true);
    }

    function it_reverse_transforms_strings_into_boolean()
    {
        $this->transform('1')->shouldReturn(true);
        $this->transform('0')->shouldReturn(false);
    }
}
