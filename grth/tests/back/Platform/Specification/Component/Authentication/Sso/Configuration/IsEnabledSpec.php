<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\IsEnabled;
use PhpSpec\ObjectBehavior;

class IsEnabledSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(true);
        $this->shouldHaveType(IsEnabled::class);
    }

    function it_can_be_represented_as_a_boolean()
    {
        $this->beConstructedWith(true);
        $this->toBoolean()->shouldReturn(true);
    }
}
