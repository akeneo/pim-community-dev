<?php

namespace spec\Akeneo\Component\FileTransformer\Options\Image;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ThumbnailOptionsResolverSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_options_are_wrong()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "thumbnail" transformation.'
            )
        )->during('resolve', [['wrong']]);
    }

    function it_throws_an_exception_when_height_or_with_are_not_int()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "thumbnail" transformation.'
            )
        )->during('resolve', [['width' => '100 px', 'height' => 100]]);

        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "thumbnail" transformation.'
            )
        )->during('resolve', [['height' => '100 px', 'width' => 100]]);
    }

    function it_resolves_valid_options()
    {
        $this->resolve(['height' => 100, 'width' => 100]);
    }
}
