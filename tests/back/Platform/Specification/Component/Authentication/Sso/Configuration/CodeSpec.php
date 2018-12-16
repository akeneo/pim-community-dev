<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Code;
use PhpSpec\ObjectBehavior;

class CodeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('jambon');
        $this->shouldHaveType(Code::class);
    }

    function it_can_be_represented_as_string()
    {
        $this->beConstructedWith('jambon');
        $this->__toString()->shouldReturn('jambon');
    }
}
