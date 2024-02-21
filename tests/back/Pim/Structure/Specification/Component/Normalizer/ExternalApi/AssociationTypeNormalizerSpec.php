<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\ExternalApi;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Normalizer\ExternalApi\AssociationTypeNormalizer;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AssociationTypeNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $stdNormalizer)
    {
        $this->beConstructedWith($stdNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationTypeNormalizer::class);
    }

    function it_supports_an_association_type(AssociationTypeInterface $associationType)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'external_api')->shouldReturn(false);
        $this->supportsNormalization($associationType, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($associationType, 'external_api')->shouldReturn(true);
    }

    function it_normalize_an_association_type_without_any_label($stdNormalizer, AssociationTypeInterface $associationType)
    {
        $data = [
            'code'   => 'X_SELL',
            'labels' => [],
        ];

        $stdNormalizer->normalize($associationType, 'standard', [])->willReturn($data);

        $normalizedAssociationType = $this->normalize($associationType, 'external_api', []);
        $normalizedAssociationType->shouldHaveLabels($data);
    }

    function it_normalize_an_association_type_with_labels($stdNormalizer, AssociationTypeInterface $associationType)
    {
        $data = [
            'code'   => 'X_SELL',
            'labels' => [
                'en_US' => 'Cross sell',
                'fr_FR' => 'Vente croisée',
            ],
        ];

        $stdNormalizer->normalize($associationType, 'standard', [])->willReturn($data);
        $this->normalize($associationType, 'external_api', [])->shouldReturn($data);
    }

    public function getMatchers(): array
    {
        return [
            'haveLabels' => function ($subject) {
                return is_object($subject['labels']);
            },
        ];
    }
}
