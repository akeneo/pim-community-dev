<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Pim\Bundle\VersioningBundle\Normalizer\Flat\ProductValueNormalizer;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer;
use Prophecy\Argument;

class VariantGroupNormalizerSpec extends ObjectBehavior
{
    function let(
        VariantGroupNormalizer $variantGroupNormalizerStandard,
        ProductValueNormalizer $productValueNormalizerStandard,
        TranslationNormalizer $translationNormalizerStandard
    ) {
        $this->beConstructedWith(
            $variantGroupNormalizerStandard,
            $productValueNormalizerStandard,
            $translationNormalizerStandard
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\VariantGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_flat_normalization(GroupInterface $group)
    {
        $this->supportsNormalization($group, 'flat')->shouldReturn(true);
        $this->supportsNormalization($group, 'csv')->shouldReturn(false);
    }

    function it_normalizes_variant_groups(
        VariantGroupNormalizer $variantGroupNormalizerStandard,
        ProductValueNormalizer $productValueNormalizerStandard,
        TranslationNormalizer $translationNormalizerStandard,
        GroupInterface $group
    ) {
        $variantGroupNormalizerStandard->normalize($group, 'standard', [])->willReturn(
            [
                'code'   => 'my_variant_group',
                'type'   => 'VARIANT',
                'axes'   => ['axe1', 'axe2'],
                'labels' => [
                    'en_US' => 'My variant group',
                    'fr_FR' => 'Mon groupe de variante',
                ],
                'values' => [
                    'a_text' => [
                        'locale' => null,
                        'scope' => null,
                        'data' => 'the text'
                    ]
                ]
            ]
        );

        $productValueNormalizerStandard->normalize(Argument::cetera())->willReturn(
            [
                'a_text' => 'the text'
            ]
        );

        $translationNormalizerStandard->normalize(Argument::cetera())->willReturn(
            [
                'label-en_US' => 'My variant group',
                'label-fr_FR' => 'Mon groupe de variante'
            ]
        );

        $this->normalize($group, 'flat')->shouldReturn(
            [
                'code'   => 'my_variant_group',
                'type'   => 'VARIANT',
                'axes'   => 'axe1,axe2',
                'a_text' => 'the text',
                'label-en_US' => 'My variant group',
                'label-fr_FR' => 'Mon groupe de variante'
            ]
        );
    }
}

