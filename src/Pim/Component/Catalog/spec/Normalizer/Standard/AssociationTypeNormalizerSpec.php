<?php

namespace spec\Pim\Component\Catalog\Normalizer\Standard;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Normalizer\Standard\TranslationNormalizer;

class AssociationTypeNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $translationNormalizer)
    {
        $this->beConstructedWith($translationNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Normalizer\Standard\AssociationTypeNormalizer');
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

        $this->normalize($associationType)->shouldReturn([
            'code'   => 'my_code',
            'labels' => []
        ]);
    }
}
