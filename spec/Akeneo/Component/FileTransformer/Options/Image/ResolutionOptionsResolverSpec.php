<?php

namespace spec\Akeneo\Component\FileTransformer\Options\Image;

use Akeneo\Component\FileTransformer\Exception\InvalidOptionsTransformationException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ResolutionOptionsResolverSpec extends ObjectBehavior
{
    function it_throws_an_exception_when_options_are_wrong()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "resolution" transformation.'
            )
        )->during('resolve', [['wrong']]);
    }

    function it_throws_an_exception_when_options_are_not_of_the_good_type()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "resolution" transformation.'
            )
        )->during('resolve', [['resolution' => '100 px', 'resolution-unit' => 100]]);
    }

    function it_throws_an_exception_when_resolution_unit_is_unknown()
    {
        $this->shouldThrow(
            new InvalidOptionsTransformationException(
                'Your options does not fulfil the requirements of the "resolution" transformation.'
            )
        )->during('resolve', [['resolution' => 100, 'resolution-unit' => 'unknown']]);
    }

    function it_resolves_valid_options()
    {
        $this->resolve(['resolution' => 100, 'resolution-unit' => 'ppc']);
        $this->resolve(['resolution' => 100, 'resolution-unit' => 'ppi']);
        $this->resolve(['resolution' => 100]);
    }
}
