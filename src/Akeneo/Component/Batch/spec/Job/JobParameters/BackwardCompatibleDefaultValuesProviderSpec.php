<?php

namespace spec\Akeneo\Component\Batch\Job\JobParameters;

use PhpSpec\ObjectBehavior;

class BackwardCompatibleDefaultValuesProviderSpec extends ObjectBehavior
{
    function it_is_a_default_values_provider()
    {
        $this->beConstructedWith(['default_value' => 'default_field']);
        $this->shouldImplement('Akeneo\Component\Batch\Job\JobParameters\DefaultValuesProviderInterface');
    }

    function it_provides_default_values()
    {
        $this->beConstructedWith(['default_value' => 'default_field']);
        $this->getDefaultValues()->shouldReturn(['default_value' => 'default_field']);
    }
}
