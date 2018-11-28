<?php

namespace Specification\Akeneo\Platform\Component\Authentication\Sso\Configuration;

use Akeneo\Platform\Component\Authentication\Sso\Configuration\Url;
use PhpSpec\ObjectBehavior;

class UrlSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Url::class);
    }

    function it_accepts_a_valid_url()
    {
        $this->beConstructedThrough('fromString', ['http://www.jambon.com/']);
    }

    function it_rejects_an_invalid_url()
    {
        $this->beConstructedThrough('fromString', ['jambon']);
        $this->shouldThrow(new \InvalidArgumentException('Value must be a valid url, "jambon" given.'))
            ->duringInstantiation();
    }
}
