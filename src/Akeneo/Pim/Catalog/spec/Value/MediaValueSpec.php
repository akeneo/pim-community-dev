<?php

namespace spec\Pim\Component\Catalog\Value;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;

class MediaValueSpec extends ObjectBehavior
{
    function let(AttributeInterface $attribute, FileInfoInterface $fileInfo)
    {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $this->beConstructedWith($attribute, 'ecommerce', 'en_US', $fileInfo);
    }

    function it_returns_data($fileInfo)
    {
        $this->getData()->shouldBeAnInstanceOf(FileInfoInterface::class);
        $this->getData()->shouldReturn($fileInfo);
    }
}
