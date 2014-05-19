<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use PimEnterprise\Bundle\WorkflowBundle\Util\ProductValueKeyGenerator;
use Symfony\Component\Serializer\SerializerInterface;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(ProductValueKeyGenerator $keyGen)
    {
        $this->beConstructedWith($keyGen);
    }

    function it_is_a_serializer_aware_normalizer_and_denormalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_supports_normalization_of_product_in_the_proposal_format(Model\ProductInterface $product)
    {
        $this->supportsNormalization($product, 'proposal')->shouldBe(true);
    }

    function it_normalizes_product_values(
        $keyGen,
        SerializerInterface $serializer,
        Model\AbstractProduct $product,
        Model\AbstractProductValue $skuVal,
        Model\AbstractProductValue $nameVal
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $product->getValues()->willReturn([$skuVal, $nameVal]);
        $keyGen->generate($skuVal)->willReturn('skuKey');
        $keyGen->generate($nameVal)->willReturn('nameKey');
        $serializer->normalize($skuVal, 'proposal', [])->willReturn('skuVal');
        $serializer->normalize($nameVal, 'proposal', [])->willReturn('nameVal');

        $this->setSerializer($serializer);
        $this->normalize($product, 'proposal')->shouldReturn(
            [
                'skuKey' => 'skuVal',
                'nameKey' => 'nameVal',
            ]
        );
    }

    function it_supports_denormalization_of_product_in_the_proposal_format()
    {
        $this->supportsDenormalization([], 'product', 'proposal')->shouldBe(true);
    }

    function it_denormalizes_product_values(
        $keyGen,
        SerializerInterface $serializer,
        Model\AbstractProduct $product,
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $keyGen->getPart('foo', ProductValueKeyGenerator::CODE)->willReturn('foo');
        $keyGen->getPart('foo', ProductValueKeyGenerator::LOCALE)->willReturn(null);
        $keyGen->getPart('foo', ProductValueKeyGenerator::SCOPE)->willReturn(null);
        $product->getValue('foo', null, null)->willReturn($value);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('custom_type');

        $serializer->denormalize('bar', 'value', 'proposal', ['instance' => $value])->shouldBeCalled();

        $this->setSerializer($serializer);
        $this->denormalize(['foo' => 'bar'], 'product', 'proposal', ['instance' => $product]);
    }

    function it_throws_exception_when_product_value_cannot_be_resolved_during_denormalization(
        $keyGen,
        Model\AbstractProduct $product,
        Model\AbstractProductValue $value
    ) {
        $keyGen->getPart('foo', ProductValueKeyGenerator::CODE)->willReturn('foo');
        $keyGen->getPart('foo', ProductValueKeyGenerator::LOCALE)->willReturn(null);
        $keyGen->getPart('foo', ProductValueKeyGenerator::SCOPE)->willReturn(null);
        $product->getValue('foo', null, null)->willReturn(null);

        $this->shouldThrow(new \Exception('Cannot find value for "foo"'))->duringDenormalize(['foo' => 'bar'], 'product', 'proposal', ['instance' => $product]);
    }
}
