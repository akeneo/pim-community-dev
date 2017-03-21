<?php

namespace spec\Pim\Component\Api\Hal;

use PhpSpec\ObjectBehavior;

class LinkSpec extends ObjectBehavior
{
    function let(
    ) {
        $this->beConstructedWith('self', 'http://akeneo.com');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Api\Hal\Link');
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
