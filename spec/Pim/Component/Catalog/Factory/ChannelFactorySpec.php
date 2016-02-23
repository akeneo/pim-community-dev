<?php

namespace spec\Pim\Component\Catalog\Factory;

use PhpSpec\ObjectBehavior;

class ChannelFactorySpec extends ObjectBehavior
{
    const CHANNEL_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Channel';

    function let()
    {
        $this->beConstructedWith(self::CHANNEL_CLASS);
    }

    function it_creates_a_channel()
    {
        $this->create()->shouldReturnAnInstanceOf(self::CHANNEL_CLASS);
    }
}
