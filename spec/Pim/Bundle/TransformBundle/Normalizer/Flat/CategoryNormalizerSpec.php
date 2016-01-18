<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Akeneo\Component\Classification\Model\CategoryInterface;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
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
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Flat\CategoryNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_category_normalization_into_csv($clothes)
    {
        $this->supportsNormalization($clothes, 'csv')->shouldBe(true);
        $this->supportsNormalization($clothes, 'json')->shouldBe(false);
        $this->supportsNormalization($clothes, 'xml')->shouldBe(false);
    }

    function it_normalizes_category(
        $transnormalizer,
        $clothes,
        CategoryInterface $catalog
    ) {
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
