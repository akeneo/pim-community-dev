<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Akeneo\Pim\Structure\Component\Normalizer\Standard\FamilyVariantNormalizer;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyVariantNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $transNormalizer)
    {
        $this->beConstructedWith($transNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_normalizes_a_family_variant(
        $transNormalizer,
        FamilyVariantInterface $familyVariant,
        FamilyInterface $family,
        Collection $variantAttributeSets,
        Collection $normalizedVariantAttributeSets
    ) {
        $familyVariant->getCode()->willReturn('family_variant');

        $transNormalizer->normalize(Argument::cetera())->willReturn([]);

        $familyVariant->getFamily()->willReturn($family);
        $family->getCode()->willReturn('family');

        $familyVariant->getVariantAttributeSets()->willReturn($variantAttributeSets);
        $variantAttributeSets->map(Argument::cetera())->willReturn($normalizedVariantAttributeSets);
        $normalizedVariantAttributeSets->toArray()->willReturn([
            [
                'level' => 1,
                'axes' => ['a_simple_select'],
                'attributes' => ['an_attribute', 'another_attribute'],
            ],
            [
                'level' => 2,
                'axes' => ['a_simple_reference_data', 'a_boolean'],
                'attributes' => ['an_identifier'],
            ],
        ]);

        $this->normalize($familyVariant, 'standard', [])->shouldReturn([
            'code' => 'family_variant',
            'labels' => [],
            'family' => 'family',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['a_simple_select'],
                    'attributes' => ['an_attribute', 'another_attribute'],
                ],
                [
                    'level' => 2,
                    'axes' => ['a_simple_reference_data', 'a_boolean'],
                    'attributes' => ['an_identifier'],
                ],
            ],
        ]);
    }
}
