<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\ProductTemplateInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;

class VariantGroupNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(GroupInterface $variantGroup, GroupTypeInterface $variantGroupType)
    {
        $variantGroup->getType()->willReturn($variantGroupType);

        $variantGroupType->isVariant()->willReturn(false);
        $this->supportsNormalization($variantGroup, 'standard')->shouldReturn(false);

        $variantGroupType->isVariant()->willReturn(true);
        $this->supportsNormalization($variantGroup, 'standard')->shouldReturn(true);

        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($variantGroup, 'xml')->shouldReturn(false);
        $this->supportsNormalization($variantGroup, 'json')->shouldReturn(false);
    }

    function it_normalizes_variant_group(
        $translationNormalizer,
        GroupInterface $variantGroup,
        GroupTypeInterface $variantGroupType,
        ProductTemplateInterface $productTemplate,
        AttributeInterface $color,
        AttributeInterface $size
    ) {
        $translationNormalizer->normalize($variantGroup, 'standard', [])->willReturn(['labels' => []]);

        $variantGroup->getCode()->willReturn('my_code');
        $variantGroup->getType()->willReturn($variantGroupType);
        $variantGroupType->getCode()->willReturn('VARIANT');

        $variantGroup->getAxisAttributes()->willReturn([$color, $size]);
        $color->getCode()->willReturn('red');
        $size->getCode()->willReturn('XL');

        $variantGroup->getProductTemplate()->willReturn($productTemplate);
        $values = [
            [
                'name' => [
                    'locale' => null,
                    'scope' => null,
                    'data' => 'Tshirt'
                ]
            ]
        ];
        $productTemplate->getValuesData()->willReturn($values);

        $this->normalize($variantGroup)->shouldReturn([
            'code'   => 'my_code',
            'type'   => 'VARIANT',
            'axes'   => ['XL', 'red'],
            'values' => $values,
            'labels' => []
        ]);
    }
}
