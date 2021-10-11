<?php

namespace Specification\Akeneo\FreeTrial\Infrastructure;

use PhpSpec\ObjectBehavior;

class RetrievePimFQDNSpec extends ObjectBehavior
{
    public function it_should_throw_error_when_url_is_not_valid()
    {
        $this->beConstructedWith('invalid_url');
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_fqdn_from_valid_url()
    {
        $this->beConstructedWith('http://valid.url');
        $this->__invoke()->shouldReturn('valid.url');
    }
}
