<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\DataTransformer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateValuesTransformerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        CustomAttributeRepository $attributeRepository
    ) {
        $this->beConstructedWith($normalizer, $denormalizer, $attributeRepository, 'ProductValue');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\DataTransformer\ProductTemplateValuesTransformer');
    }

    function it_is_a_data_transformer()
    {
        $this->shouldImplement('Symfony\Component\Form\DataTransformerInterface');
    }

    function it_can_transform_normalized_values_into_value_objects(
        $denormalizer,
        $attributeRepository,
        AttributeInterface $attribute,
        ProductValueInterface $value
    ) {
        $data = [
            'name' => [
                [
                    'locale' => null,
                    'scope'  => null,
                    'value'  => 'Foo'
                ]
            ]
        ];

        $attributeRepository->findOneByCode('name')->willReturn($attribute);

        $denormalizer
            ->denormalize($data['name'][0], 'ProductValue', 'json', ['attribute' => $attribute])
            ->shouldBeCalled()
            ->willReturn($value);

        $values = $this->transform($data);

        $values->shouldHaveCount(1);
        $values[0]->shouldBe($value);
    }

    function it_can_reverse_transform_value_objects_into_normalized_values(
        $normalizer,
        AttributeInterface $attribute,
        ProductValueInterface $value
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('name');

        $normalizer
            ->normalize($value, 'json', ['entity' => 'product'])
            ->shouldBeCalled()
            ->willReturn(
                [
                    'locale' => 'en_US',
                    'scope' => 'ecommerce',
                    'value' => 'Foo'
                ]
            );

        $this
            ->reverseTransform([$value])
            ->shouldReturn(
                [
                    'name' => [
                        [
                            'locale' => 'en_US',
                            'scope'  => 'ecommerce',
                            'value'  => 'Foo'
                        ]
                    ]
                ]
            );
    }
}

class CustomAttributeRepository extends AttributeRepository
{
    public function findOneByCode()
    {
    }
}
