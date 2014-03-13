<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\TransformBundle\Normalizer\TranslationNormalizer;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_in_bson_of_family(Family $family)
    {
        $this->supportsNormalization($family, 'bson')->shouldBe(true);
        $this->supportsNormalization($family, 'json')->shouldBe(false);
        $this->supportsNormalization($family, 'xml')->shouldBe(false);
    }

    function it_normalizes_family(
        TranslationNormalizer $normalizer,
        Family $family
    ) {
        $family->getCode()->willReturn('mongo');
        $normalizer->normalize($family, 'bson', [])->willReturn(['label' => 'translations']);

        $this->normalize($family, 'bson', [])->shouldReturn([
            'code' => 'mongo',
            'label' => 'translations'
        ]);
    }
}
