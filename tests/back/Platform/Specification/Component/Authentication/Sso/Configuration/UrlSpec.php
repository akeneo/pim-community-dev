<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use PhpSpec\ObjectBehavior;

class UrlSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('http://www.jambon.com/');
        $this->shouldHaveType(Url::class);
    }

    function it_accepts_a_valid_url()
    {
        $this->beConstructedWith('http://www.jambon.com/');
    }

    function it_rejects_an_invalid_url()
    {
        $this->beConstructedWith('jambon');
        $this->shouldThrow(new \InvalidArgumentException('Value must be a valid URL, "jambon" given.'))
            ->duringInstantiation();
    }

    function it_can_be_represented_as_string()
    {
        $this->beConstructedWith('http://www.jambon.com/');
        $this->__toString()->shouldReturn('http://www.jambon.com/');
    }
}
