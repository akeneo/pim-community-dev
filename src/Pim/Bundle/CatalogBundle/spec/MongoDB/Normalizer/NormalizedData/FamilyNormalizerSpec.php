<?php

namespace spec\Pim\Bundle\CatalogBundle\MongoDB\Normalizer\NormalizedData;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;

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

    function it_supports_normalization_in_mongodb_json_of_family(FamilyInterface $family)
    {
        $this->supportsNormalization($family, 'mongodb_json')->shouldBe(true);
        $this->supportsNormalization($family, 'json')->shouldBe(false);
        $this->supportsNormalization($family, 'xml')->shouldBe(false);
    }

    function it_normalizes_family(
        $normalizer,
        FamilyInterface $family,
        AttributeInterface $sku
    ) {
        $sku->getCode()->willReturn('sku');
        $family->getCode()->willReturn('mongo');
        $family->getAttributeAsLabel()->willReturn($sku);
        $normalizer->normalize($family, 'mongodb_json', [])->willReturn(['label' => 'translations']);

        $this->normalize($family, 'mongodb_json', [])->shouldReturn([
            'code' => 'mongo',
            'label' => 'translations',
            'attributeAsLabel' => 'sku'
        ]);
    }
}
