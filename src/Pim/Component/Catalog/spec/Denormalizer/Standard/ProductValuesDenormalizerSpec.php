<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\ProductValue\ScalarProductValue;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductValuesDenormalizerSpec extends ObjectBehavior
{
    function let(
        DenormalizerInterface $denormalizer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($denormalizer, $attributeRepository, ProductValue::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Standard\ProductValuesDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_a_collection_of_product_values_from_json()
    {
        $this->supportsDenormalization([], 'ProductValue[]', 'standard')->shouldReturn(true);
        $this->supportsDenormalization([], 'foo', 'standard')->shouldReturn(false);
        $this->supportsDenormalization([], 'ProductValue[]', 'csv')->shouldReturn(false);
    }

    function it_denormalizes_product_values_from_json(
        $denormalizer,
        $attributeRepository,
        ProductValueInterface $nameValue,
        ProductValueInterface $colorValue,
        AttributeInterface $name,
        AttributeInterface $color
    ) {
        $data = [
            'name' => [
                ['locale' => null, 'scope' => null, 'value' => 'foo']
            ],
            'color' => [
                ['locale' => 'en_US', 'scope' => 'ecommerce', 'value' => 'red']
            ]
        ];

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $name->getCode()->willReturn('name');
        $nameValue->getAttribute()->willReturn($name);
        $nameValue->getScope()->willReturn(null);
        $nameValue->getLocale()->willReturn(null);

        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $color->getCode()->willReturn('color');
        $colorValue->getAttribute()->willReturn($color);
        $colorValue->getScope()->willReturn('ecommerce');
        $colorValue->getLocale()->willReturn('en_US');

        $denormalizer
            ->denormalize($data['name'][0], ProductValue::class, 'standard', ['attribute' => $name])
            ->shouldBeCalled()
            ->willReturn($nameValue);

        $denormalizer
            ->denormalize($data['color'][0], ProductValue::class, 'standard', ['attribute' => $color])
            ->shouldBeCalled()
            ->willReturn($colorValue);

        $values = $this->denormalize($data, 'ProductValue[]', 'standard');

        $values->shouldHaveCount(2);
        $values->getByKey('name-<all_channels>-<all_locales>')->shouldBe($nameValue);
        $values->getByKey('color-ecommerce-en_US')->shouldBe($colorValue);
    }
}
