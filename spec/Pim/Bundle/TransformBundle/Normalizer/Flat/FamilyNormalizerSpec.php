<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\TransformBundle\Normalizer\Flat\TranslationNormalizer;
use Prophecy\Argument;

class FamilyNormalizerSpec extends ObjectBehavior
{
    function let(
        TranslationNormalizer $transnormalizer,
        FamilyInterface $family
    ) {
        $this->beConstructedWith($transnormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Flat\FamilyNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_family_normalization_into_csv($family)
    {
        $this->supportsNormalization($family, 'csv')->shouldBe(true);
        $this->supportsNormalization($family, 'json')->shouldBe(false);
        $this->supportsNormalization($family, 'xml')->shouldBe(false);
    }

    function it_normalizes_family(
        $transnormalizer,
        $family,
        AttributeInterface $name,
        AttributeInterface $price,
        AttributeRequirement $ecommercereq,
        AttributeRequirement $mobilereq,
        ChannelInterface $ecommerce,
        ChannelInterface $mobile
    ) {
        $transnormalizer->normalize(Argument::cetera())->willReturn([]);
        $family->getCode()->willReturn('mugs');
        $family->getAttributes()->willReturn([$name, $price]);
        $name->getCode()->willReturn('name');
        $price->getCode()->willReturn('price');
        $family->getAttributeAsLabel()->willReturn($name);
        $family->getAttributeRequirements()->willReturn([$ecommercereq, $mobilereq]);
        $ecommercereq->getChannel()->willReturn($ecommerce);
        $mobilereq->getChannel()->willReturn($mobile);
        $ecommercereq->isRequired()->willReturn(true);
        $mobilereq->isRequired()->willReturn(false);
        $ecommerce->getCode()->willReturn('ecommerce');
        $mobile->getCode()->willReturn('mobile');
        $ecommercereq->getAttribute()->willReturn($name);

        $result = $this->normalize($family)->shouldReturn(
            [
                'code'             => 'mugs',
                'attributes'       => 'name,price',
                'attributeAsLabel' => 'name',
                'requirements'     => 'ecommerce:name|mobile:',
            ]
        );
    }
}
