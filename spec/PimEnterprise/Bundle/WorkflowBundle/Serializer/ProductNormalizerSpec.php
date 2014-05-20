<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use PimEnterprise\Bundle\WorkflowBundle\Util\ProductValueKeyGenerator;

class ProductNormalizerSpec extends ObjectBehavior
{
    function let(
        ProductBuilder $builder,
        AttributeRepository $repository,
        ProductValueKeyGenerator $keyGen)
    {
        $this->beConstructedWith($builder, $repository, $keyGen);
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

    function it_creates_value_on_the_fly_when_it_cannot_be_resolved(
        $keyGen,
        $builder,
        $repository,
        SerializerInterface $serializer,
        Model\AbstractProduct $product,
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $granularity
    ) {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $keyGen->getPart('granularity', ProductValueKeyGenerator::CODE)->willReturn('granularity');
        $keyGen->getPart('granularity', ProductValueKeyGenerator::LOCALE)->willReturn('en_US');
        $keyGen->getPart('granularity', ProductValueKeyGenerator::SCOPE)->willReturn(null);
        $product->getValue('granularity', 'en_US', null)->willReturn(null);
        $repository->findOneBy(['code' => 'granularity'])->willReturn($granularity);
        $builder->addProductValue($product, $granularity, 'en_US', null)->willReturn($value);

        $serializer->denormalize(80, 'value', 'proposal', ['instance' => $value])->shouldBeCalled();

        $this->setSerializer($serializer);
        $this->denormalize(['granularity' => 80], 'product', 'proposal', ['instance' => $product]);
    }
}
