<?php

namespace spec\Pim\Component\Connector\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Connector\Normalizer\TranslationNormalizer;
use Prophecy\Argument;

class AssociationTypeNormalizerSpec extends ObjectBehavior
{
    function let(TranslationNormalizer $transnormalizer)
    {
        $this->beConstructedWith($transnormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Normalizer\AssociationTypeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_association_type_normalization_into_csv(AssociationTypeInterface $association)
    {
        $this->supportsNormalization($association, 'csv')->shouldBe(true);
        $this->supportsNormalization($association, 'json')->shouldBe(false);
        $this->supportsNormalization($association, 'xml')->shouldBe(false);
    }

    function it_normalizes_association_type($transnormalizer, AssociationTypeInterface $association)
    {
        $transnormalizer->normalize(Argument::cetera())->willReturn([]);
        $association->getCode()->willReturn('PACK');
        $this->normalize($association)->shouldReturn(
            [
                'code' => 'PACK'
            ]
        );
    }
}
