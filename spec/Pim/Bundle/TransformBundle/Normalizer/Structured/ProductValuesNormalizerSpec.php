<?php

namespace spec\Pim\Bundle\TransformBundle\Normalizer\Structured;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValuesNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Normalizer\Structured\ProductValuesNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_normalization_of_a_collection_of_product_values_into_json()
    {
        $this->supportsNormalization(new ArrayCollection(), 'json')->shouldReturn(true);
        $this->supportsNormalization('foo', 'json')->shouldReturn(false);
        $this->supportsNormalization(new ArrayCollection(), 'csv')->shouldReturn(false);
    }

    function it_normalizes_product_values_into_json(
        $serializer,
        ProductValueInterface $nameValue,
        ProductValueInterface $colorValue,
        AttributeInterface $name,
        AttributeInterface $color
    ) {
        $nameValue->getAttribute()->willReturn($name);
        $colorValue->getAttribute()->willReturn($color);
        $name->getCode()->willReturn('name');
        $color->getCode()->willReturn('color');

        $serializer
            ->normalize($nameValue, 'json', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => null, 'scope' => null, 'value' => 'foo']);

        $serializer
            ->normalize($colorValue, 'json', [])
            ->shouldBeCalled()
            ->willReturn(['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'red']);

        $this
            ->normalize([$nameValue, $colorValue], 'json')
            ->shouldReturn(
                [
                    'name' => [
                        ['locale' => null, 'scope' => null, 'value' => 'foo']
                    ],
                    'color' => [
                        ['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'red']
                    ]
                ]
            );
    }
}
