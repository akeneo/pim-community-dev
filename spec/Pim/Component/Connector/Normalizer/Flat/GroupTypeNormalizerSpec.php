<?php

namespace spec\Pim\Component\Connector\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Connector\Normalizer\Flat\TranslationNormalizer;
use Prophecy\Argument;

class GroupTypeNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transnormalizer,
        GroupTypeInterface $groupType
    ) {
        $this->beConstructedWith($transnormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Connector\Normalizer\Flat\GroupTypeNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_group_type_normalization_into_csv(GroupTypeInterface $currency)
    {
        $this->supportsNormalization($currency, 'csv')->shouldBe(true);
        $this->supportsNormalization($currency, 'flat')->shouldBe(true);
        $this->supportsNormalization($currency, 'json')->shouldBe(false);
        $this->supportsNormalization($currency, 'xml')->shouldBe(false);
    }

    function it_normalizes_group_type(
        $transnormalizer,
        GroupTypeInterface $groupType
    ) {
        $transnormalizer->normalize(Argument::cetera())->willReturn([]);
        $groupType->getCode()->willReturn('RELATED');
        $groupType->isVariant()->willReturn(false);

        $this->normalize($groupType)->shouldReturn(
            [
                'code'      => 'RELATED',
                'is_variant' => 0,
            ]
        );
    }
}
