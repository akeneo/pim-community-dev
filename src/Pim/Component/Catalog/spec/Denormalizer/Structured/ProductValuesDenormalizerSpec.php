<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductValuesDenormalizerSpec extends ObjectBehavior
{
    function let(
        DenormalizerInterface $denormalizer,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->beConstructedWith($denormalizer, $attributeRepository, 'ProductValue');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Structured\ProductValuesDenormalizer');
    }

    function it_is_a_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_a_collection_of_product_values_from_json()
    {
        $this->supportsDenormalization([], 'ProductValue[]', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'foo', 'json')->shouldReturn(false);
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
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);

        $denormalizer
            ->denormalize($data['name'][0], 'ProductValue', 'json', ['attribute' => $name])
            ->shouldBeCalled()
            ->willReturn($nameValue);

        $denormalizer
            ->denormalize($data['color'][0], 'ProductValue', 'json', ['attribute' => $color])
            ->shouldBeCalled()
            ->willReturn($colorValue);

        $values = $this->denormalize($data, 'ProductValue[]', 'json');

        $values->shouldHaveCount(2);
        $values[0]->shouldBe($nameValue);
        $values[1]->shouldBe($colorValue);
    }
}
