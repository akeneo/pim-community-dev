<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\FamilyVariantNormalizer;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FamilyVariantNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FamilyVariantNormalizer::class);
    }

    function it_supports_a_family_variant(FamilyVariantInterface $familyVariant)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($familyVariant, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($familyVariant, 'external_api')->shouldReturn(true);
    }

    function it_normalizes_a_family_variant($stdNormalizer, FamilyVariantInterface $familyVariant)
    {
        $data = [
            'code' => 'my_family_variant',
            'family' => 'my_family',
            'labels' => [],
            'variant_attribute_sets' => [
                'level' => 1,
                'attributes' => ['a_simple_slect', 'a_text'],
                "axes" => ['a_simple_select']
            ]
        ];

        $stdNormalizer->normalize($familyVariant, 'standard', [])->willReturn($data);

        $normalizedFamily = $this->normalize($familyVariant, 'external_api', []);
        $normalizedFamily->shouldReturnApiFormat($normalizedFamily);
    }

    public function getMatchers(): array
    {
        return [
            'returnApiFormat' => function ($subject) {
                $variantAttributeSets = ['level' => 1, 'attributes' => ['a_simple_slect', 'a_text'], "axes" => ['a_simple_select']];

                return is_object($subject['labels'])
                    && !array_key_exists('family', $subject)
                    && 'my_family_variant' === $subject['code']
                    && $variantAttributeSets === $subject['variant_attribute_sets']
                ;
            }
        ];
    }
}
