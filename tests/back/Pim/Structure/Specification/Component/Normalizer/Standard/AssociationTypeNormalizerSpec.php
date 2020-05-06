<?php

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer\Standard;

use Akeneo\Pim\Structure\Component\Normalizer\Standard\AssociationTypeNormalizer;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\TranslationNormalizer;

class AssociationTypeNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer, FeatureFlag $quantifiedAssociationFeatureFlag)
    {
        $quantifiedAssociationFeatureFlag->isEnabled()->willReturn(false);

        $this->beConstructedWith($translationNormalizer, $quantifiedAssociationFeatureFlag);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AssociationTypeNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(AssociationTypeInterface $associationType)
    {
        $this->supportsNormalization($associationType, 'standard')->shouldReturn(true);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
        $this->supportsNormalization($associationType, 'xml')->shouldReturn(false);
        $this->supportsNormalization($associationType, 'json')->shouldReturn(false);
    }

    function it_normalizes_association_type(
        $translationNormalizer,
        AssociationTypeInterface $associationType
    ) {
        $translationNormalizer->normalize($associationType, 'standard', [])->willReturn([]);

        $associationType->getCode()->willReturn('my_code');
        $associationType->isTwoWay()->willReturn(true);

        $this->normalize($associationType)->shouldReturn([
            'code'   => 'my_code',
            'labels' => [],
            'is_two_way' => true,
        ]);
    }

    function it_normalize_is_quantified_only_when_feature_flag_is_enabled(
        $translationNormalizer,
        AssociationTypeInterface $associationType,
        FeatureFlag $quantifiedAssociationFeatureFlag
    ) {
        $quantifiedAssociationFeatureFlag->isEnabled()->willReturn(true);
        $translationNormalizer->normalize($associationType, 'standard', [])->willReturn([]);

        $associationType->getCode()->willReturn('my_code');
        $associationType->isTwoWay()->willReturn(false);
        $associationType->isQuantified()->willReturn(true);

        $this->normalize($associationType)->shouldReturn([
            'code'   => 'my_code',
            'labels' => [],
            'is_two_way' => false,
            'is_quantified' => true,
        ]);
    }
}
