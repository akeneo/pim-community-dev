<?php

namespace spec\Akeneo\Component\FileTransformer\Options\Image;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ColorSpaceOptionsResolverSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_options_are_wrong()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "colorspace" transformation.'
            )
        )->during('resolve', [['wrong']]);
    }

    function it_throws_an_exception_when_colorspace_is_unknown()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "colorspace" transformation.'
            )
        )->during('resolve', [['colorspace' => 'unknown']]);
    }

    function it_resolves_valid_options()
    {
        $this->resolve(['colorspace' => 'cmyk']);
        $this->resolve(['colorspace' => 'rgb']);
        $this->resolve(['colorspace' => 'gray']);
    }
}
