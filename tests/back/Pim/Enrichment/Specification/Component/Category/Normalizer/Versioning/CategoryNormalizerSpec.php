<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Category\Normalizer\Versioning;

use Akeneo\Pim\Enrichment\Component\Category\Normalizer\Versioning\CategoryNormalizer;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\TranslationNormalizer;

use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
        $this->shouldHaveType(CategoryNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
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
