<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Channel;
use PhpSpec\ObjectBehavior;

class ChannelSpec extends ObjectBehavior
{
    function it_provides_channel_validator_name()
    {
        $this->validatedBy()->shouldReturn('pim_at_least_a_channel');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Channel::class);
    }

    function it_is_a_choice()
    {
        $this->shouldHaveType('\Symfony\Component\Validator\Constraints\Choice');
    }

    function it_is_a_constraint()
    {
        $this->shouldHaveType('\Symfony\Component\Validator\Constraint');
    }
}
