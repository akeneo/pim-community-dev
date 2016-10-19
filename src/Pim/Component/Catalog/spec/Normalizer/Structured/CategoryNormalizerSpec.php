<?php

namespace spec\Pim\Component\Catalog\Normalizer\Structured;

use Akeneo\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Normalizer\Structured\TranslationNormalizer;
use Prophecy\Argument;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transnormalizer,
        CategoryInterface $clothes
    ) {
        $this->beConstructedWith($transnormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Structured\CategoryNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_category_normalization_into_json_and_xml($clothes)
    {
        $this->supportsNormalization($clothes, 'csv')->shouldBe(false);
        $this->supportsNormalization($clothes, 'json')->shouldBe(true);
        $this->supportsNormalization($clothes, 'xml')->shouldBe(true);
    }

    function it_normalizes_category($transnormalizer, $clothes, CategoryInterface $catalog)
    {
        $transnormalizer->normalize(Argument::cetera())->willReturn([]);
        $clothes->getCode()->willReturn('clothes');
        $clothes->getParent()->willReturn($catalog);
        $catalog->getCode()->willReturn('Master catalog');

        $this->normalize($clothes)->shouldReturn(
            [
                'code'    => 'clothes',
                'parent'  => 'Master catalog'
            ]
        );
    }
}
