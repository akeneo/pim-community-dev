<?php

namespace spec\PimEnterprise\Component\ProductAsset\Connector\Factory;

use PhpSpec\ObjectBehavior;

class TagFactorySpec extends ObjectBehavior
{
    const TAG_CLASS = 'PimEnterprise\Component\ProductAsset\Model\Tag';

    function let()
    {
        $this->beConstructedWith(self::TAG_CLASS);
    }

    function it_can_be_initialized()
    {
        $this->shouldHaveType('PimEnterprise\Component\ProductAsset\Connector\Factory\TagFactory');
    }

    function it_creates_a_tag()
    {
        $this->createTag()->shouldReturnAnInstanceOf(self::TAG_CLASS);
    }
}
