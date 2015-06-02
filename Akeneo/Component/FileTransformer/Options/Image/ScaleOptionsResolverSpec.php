<?php

namespace spec\Akeneo\Component\FileTransformer\Options\Image;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ScaleOptionsResolverSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_options_are_wrong()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "scale" transformation.'
            )
        )->during('resolve', [['wrong']]);
    }

    function it_throws_an_exception_when_options_are_not_of_the_good_type()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "scale" transformation.'
            )
        )->during('resolve', [['ratio' => '100 px']]);

        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "scale" transformation.'
            )
        )->during('resolve', [['height' => '100 px']]);

        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "scale" transformation.'
            )
        )->during('resolve', [['width' => '100 px']]);
    }

    function it_throws_an_exception_when_ratio_width_and_height_are_null()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Please choose one of the among option "ratio", "width", "height" for the "scale" transformation.'
            )
        )->during('resolve', [[]]);
    }

    function it_throws_an_exception_when_ratio_is_not_a_percentage()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "scale" transformation.'
            )
        )->during('resolve', [['ratio' => 1036 ]]);
    }

    function it_resolves_valid_options()
    {
        $this->resolve(['width' => 100]);
        $this->resolve(['height' => 100]);
        $this->resolve(['ratio' => 0.36]);
    }
}
