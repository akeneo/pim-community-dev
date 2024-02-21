<?php

namespace spec\Akeneo\Tool\Component\Api\Hal;

use Akeneo\Tool\Component\Api\Hal\Link;
use PhpSpec\ObjectBehavior;

class LinkSpec extends ObjectBehavior
{
    function let(
    ) {
        $this->beConstructedWith('self', 'http://akeneo.com');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Link::class);
    }

    function it_creates_a_link()
    {
        $this->getRel()->shouldReturn('self');
        $this->getUrl()->shouldReturn('http://akeneo.com');
    }

    function it_creates_an_hal_link()
    {
        $link = [
            'self' => [
                'href' => 'http://akeneo.com',
            ],
        ];

        $this->toArray()->shouldReturn($link);
    }
}
