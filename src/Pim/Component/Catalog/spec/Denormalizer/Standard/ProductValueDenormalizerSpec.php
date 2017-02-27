<?php

namespace spec\Pim\Component\Catalog\Denormalizer\Standard;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueDenormalizerSpec extends ObjectBehavior
{
    const VALUE_CLASS = 'Pim\Component\Catalog\Model\ProductValue';

    function let(SerializerInterface $serializer, ProductValueFactory $productValueFactory)
    {
        $this->beConstructedWith($productValueFactory, self::VALUE_CLASS);

        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Component\Catalog\Denormalizer\Standard\ProductValueDenormalizer');
    }

    function it_is_a_serializer_aware_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_product_values_from_json()
    {
        $this->supportsDenormalization([], self::VALUE_CLASS, 'standard')->shouldReturn(true);
        $this->supportsDenormalization([], 'Product', 'standard')->shouldReturn(false);
        $this->supportsDenormalization([], self::VALUE_CLASS, 'csv')->shouldReturn(false);
    }

    function it_requires_attribute_to_be_passed_in_the_context()
    {
        $this
            ->shouldThrow(new InvalidArgumentException('Attribute must be passed in the context'))
            ->duringDenormalize([], self::VALUE_CLASS, 'standard', []);

        $this
            ->shouldThrow(
                new InvalidArgumentException(
                    'Attribute must be an instance of Pim\Component\Catalog\Model\AttributeInterface, string given'
                )
            )
            ->duringDenormalize([], self::VALUE_CLASS, 'standard', ['attribute' => 'foo']);
    }

    function it_denormalizes_json_into_product_values(
        $serializer,
        $productValueFactory,
        AttributeInterface $attribute,
        ProductValueInterface $productValue
    ) {
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $serializer
            ->denormalize(null, 'pim_catalog_text', 'standard', ['attribute' => $attribute])
            ->shouldBeCalled()
            ->willReturn('foo');

        $productValue->setData('foo')->shouldBeCalled();
        $productValueFactory->create($attribute, null, null)->willReturn($productValue);

        $this->denormalize(
            [],
            self::VALUE_CLASS,
            'standard',
            ['attribute' => $attribute]
        );
    }

    function it_sets_the_locale_and_scope_when_denormalizing_values(
        $serializer,
        $productValueFactory,
        AttributeInterface $attribute,
        ProductValueInterface $productValue
    ) {
        $attribute->getType()->willReturn('pim_catalog_number');
        $attribute->getBackendType()->willReturn('decimal');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $serializer
            ->denormalize(1, 'pim_catalog_number', 'standard', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(1);

        $productValue->setData(1)->shouldBeCalled();
        $productValueFactory->create($attribute, 'ecommerce', 'en_US')->willReturn($productValue);

        $this->denormalize(
            ['data' => 1, 'locale' => 'en_US', 'scope' => 'ecommerce'],
            self::VALUE_CLASS,
            'standard',
            ['attribute' => $attribute]
        );
    }
}
