<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\VersioningBundle\Normalizer\Flat\TranslationNormalizer;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Normalizer\Standard\AssociationTypeNormalizer;
use Prophecy\Argument;

class AssociationTypeNormalizerSpec extends ObjectBehavior
{
    function let(
        AssociationTypeNormalizer $associationTypeNormalizerStandard,
        TranslationNormalizer $translationNormalizer
    ) {
        $this->beConstructedWith($associationTypeNormalizerStandard, $translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\VersioningBundle\Normalizer\Flat\AssociationTypeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_association_type_normalization_into_flat(AssociationTypeInterface $association)
    {
        $this->supportsNormalization($association, 'flat')->shouldBe(true);
        $this->supportsNormalization($association, 'csv')->shouldBe(false);
        $this->supportsNormalization($association, 'json')->shouldBe(false);
        $this->supportsNormalization($association, 'xml')->shouldBe(false);
    }

    function it_normalizes_association_type(
        AssociationTypeNormalizer $associationTypeNormalizerStandard,
        TranslationNormalizer $translationNormalizer,
        AssociationTypeInterface $associationType
    ) {
        $translationNormalizer->supportsNormalization(Argument::cetera())->willReturn(true);
        $translationNormalizer->normalize(Argument::cetera())->willReturn([
            'label-en_US' => 'My association type',
            'label-fr_FR' => 'Mon type d\'association',
        ]);

        $associationTypeNormalizerStandard->supportsNormalization($associationType, 'standard')->willReturn(true);
        $associationTypeNormalizerStandard->normalize($associationType, 'standard', [])->willReturn([
            'code'   => 'PACK',
            'labels' => [
                'en_US' => 'My association type',
                'fr_FR' => 'Mon type d\'association',
            ],
        ]);

        $this->normalize($associationType, 'flat', [])->shouldReturn(
            [
                'code' => 'PACK',
                'label-en_US' => 'My association type',
                'label-fr_FR' => 'Mon type d\'association',
            ]
        );
    }
}
