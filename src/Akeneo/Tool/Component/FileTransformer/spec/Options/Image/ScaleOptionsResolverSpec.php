<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\FileTransformer\Options\Image;

use Akeneo\Tool\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use PhpSpec\ObjectBehavior;

class ScaleOptionsResolverSpec extends ObjectBehavior
{
    public function it_throws_an_exception_when_options_are_wrong(): void
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "scale" transformation.'
            )
        )->during('resolve', [['wrong']]);
    }

    public function it_throws_an_exception_when_options_are_not_of_the_good_type(): void
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

    public function it_throws_an_exception_when_ratio_width_and_height_are_null(): void
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Please choose one of the among option "ratio", "width", "height" for the "scale" transformation.'
            )
        )->during('resolve', [[]]);
    }

    public function it_throws_an_exception_when_ratio_is_not_a_percentage(): void
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'The option "ratio" of the "scale" transformation should be between 0 and 100.'
            )
        )->during('resolve', [['ratio' => 1036]]);
    }

    public function it_resolves_valid_options(): void
    {
        $this->resolve(['width' => 100]);
        $this->resolve(['height' => 100]);
        $this->resolve(['ratio' => 33]);
        $this->resolve(['ratio' => 0]);
        $this->resolve(['ratio' => 100]);
    }
}
