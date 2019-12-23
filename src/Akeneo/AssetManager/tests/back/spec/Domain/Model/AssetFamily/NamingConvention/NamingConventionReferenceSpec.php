<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use PhpSpec\ObjectBehavior;

class NamingConventionReferenceSpec extends ObjectBehavior
{
    function it_creates_a_null_convention_reference()
    {
        $this->beConstructedThrough('noNamingConvention', []);
        $this->normalize()->shouldReturn(null);
    }

    function it_creates_a_convention_reference()
    {
        $normalizedNamingConvention = [
            'source' => [
                'property' => 'code',
                'channel' => null,
                'locale' => null
            ],
            'pattern' => 'pattern',
            'strict' => true,
        ];

        $this->beConstructedThrough('createFromNormalized', [$normalizedNamingConvention]);

        $this->normalize()->shouldReturn($normalizedNamingConvention);
    }

    function it_creates_a_null_convention_reference_from_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [null]);
        $this->normalize()->shouldReturn(null);
    }

}
