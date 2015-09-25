<?php

namespace spec\Pim\Bundle\TransformBundle\Denormalizer\Structured;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueDenormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $this->beConstructedWith('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Denormalizer\Structured\ProductValueDenormalizer');
    }

    function it_is_a_serializer_aware_denormalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\SerializerAwareInterface');
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_denormalization_of_product_values_from_json()
    {
        $this->supportsDenormalization([], 'Pim\Bundle\CatalogBundle\Model\ProductValue', 'json')->shouldReturn(true);
        $this->supportsDenormalization([], 'Product', 'json')->shouldReturn(false);
        $this->supportsDenormalization([], 'Pim\Bundle\CatalogBundle\Model\ProductValue', 'csv')->shouldReturn(false);
    }

    function it_requires_attribute_to_be_passed_in_the_context()
    {
        $this
            ->shouldThrow(new InvalidArgumentException('Attribute must be passed in the context'))
            ->duringDenormalize([], 'Pim\Bundle\CatalogBundle\Model\ProductValue', 'json', []);

        $this
            ->shouldThrow(
                new InvalidArgumentException(
                    'Attribute must be an instance of Pim\Bundle\CatalogBundle\Model\AttributeInterface, string given'
                )
            )
            ->duringDenormalize([], 'Pim\Bundle\CatalogBundle\Model\ProductValue', 'json', ['attribute' => 'foo']);
    }

    function it_denormalizes_json_into_product_values($serializer, AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_text');
        $attribute->getBackendType()->willReturn('text');
        $attribute->isBackendTypeReferenceData()->willReturn(false);

        $serializer
            ->denormalize(null, 'pim_catalog_text', 'json', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn('foo');

        $value = $this->denormalize(
            [],
            'Pim\Bundle\CatalogBundle\Model\ProductValue',
            'json',
            ['attribute' => $attribute]
        );

        $value->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductValue');
        $value->getData()->shouldReturn('foo');
    }

    function it_sets_the_locale_and_scope_when_denormalizing_values($serializer, AttributeInterface $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_number');
        $attribute->getBackendType()->willReturn('decimal');
        $attribute->isBackendTypeReferenceData()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);

        $serializer
            ->denormalize(1, 'pim_catalog_number', 'json', Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn(1);

        $value = $this->denormalize(
            ['data' => 1, 'locale' => 'en_US', 'scope' => 'ecommerce'],
            'Pim\Bundle\CatalogBundle\Model\ProductValue',
            'json',
            ['attribute' => $attribute]
        );

        $value->shouldBeAnInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductValue');
        $value->getData()->shouldReturn(1);
        $value->getLocale()->shouldReturn('en_US');
        $value->getScope()->shouldReturn('ecommerce');
    }
}
