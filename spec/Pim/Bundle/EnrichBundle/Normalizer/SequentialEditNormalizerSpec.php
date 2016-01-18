<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Entity\SequentialEdit;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SequentialEditNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_supports_sequential_edits(SequentialEdit $sequentialEdit)
    {
        $this->supportsNormalization($sequentialEdit, 'internal_api')->shouldReturn(true);
    }

    function it_normalize_sequential_edits(SequentialEdit $sequentialEdit)
    {
        $sequentialEdit->getObjectSet()->willReturn(['objectSet']);

        $this->normalize($sequentialEdit)->shouldReturn([
            'objectSet' => ['objectSet']
        ]);
    }
}
