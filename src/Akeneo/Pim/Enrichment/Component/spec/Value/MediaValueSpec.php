<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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
