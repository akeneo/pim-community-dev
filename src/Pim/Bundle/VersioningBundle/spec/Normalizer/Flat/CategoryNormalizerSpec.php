<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Akeneo\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Normalizer\Standard\CategoryNormalizer;

use Prophecy\Argument;

class CategoryNormalizerSpec extends ObjectBehavior
{
    function let(
        CategoryNormalizer $categoryNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($categoryNormalizerStandard, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\CategoryNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_category_normalization_into_flat(
        CategoryInterface $clothes
    ) {
        $this->supportsNormalization($clothes, 'flat')->shouldBe(false);
        $this->supportsNormalization($clothes, 'csv')->shouldBe(false);
        $this->supportsNormalization($clothes, 'json')->shouldBe(false);
        $this->supportsNormalization($clothes, 'xml')->shouldBe(false);
    }

    function it_normalizes_category(
        CategoryNormalizer $categoryNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        CategoryInterface $clothes
    ) {
        $translationNormalizer->supportsNormalization(Argument::cetera())->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())->willReturn(
            [
                'label-en_US' => 'My category',
            ]
        );

        $categoryNormalizerStandard->supportsNormalization($clothes, 'standard')->willReturn(true);
        $categoryNormalizerStandard->normalize($clothes, 'standard', [])->willReturn(
            [
                'code'   => 'clothes',
                'parent' => 'Master catalog',
                'labels' => [
                    'en_US' => 'My category',
                ],
            ]
        );

        $this->normalize($clothes)->shouldReturn(
            [
                'code'        => 'clothes',
                'parent'      => 'Master catalog',
                'label-en_US' => 'My category',
            ]
        );
    }
}
