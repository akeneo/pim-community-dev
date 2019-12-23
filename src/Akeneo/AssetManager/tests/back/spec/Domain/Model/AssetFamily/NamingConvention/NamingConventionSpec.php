<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use PhpSpec\ObjectBehavior;

class NamingConventionSpec extends ObjectBehavior
{
    function it_is_a_naming_convention()
    {
        $this->beConstructedThrough('createFromNormalized', [
            [['source' => ['property' => 'code'], 'pattern' => 'pattern', 'strict' => true]]
        ]);
        $this->shouldBeAnInstanceOf(NamingConvention::class);
    }

    function it_cannot_be_constructed_wiithout_a_source()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['pattern' => 'pattern', 'strict' => true]]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_without_a_pattern()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['source' => ['property' => 'code'], 'strict' => true]]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_without_a_strict_option()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['source' => ['property' => 'code'], 'pattern' => 'pattern']]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['source' => ['property' => 'code'], 'pattern' => 'pattern', 'strict' => false]]
        );
        $this->normalize()->shouldReturn(
            [
                'source' => [
                    'property' => 'code',
                    'channel' => null,
                    'locale' => null,
                ],
                'pattern' => 'pattern',
                'strict' => false,
            ]
        );
    }
}
