<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use Prophecy\Argument;

class ProductRuleConditionNormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleConditionNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes(ProductConditionInterface $object)
    {
        $object->getField()->shouldBeCalled()->willReturn('name');
        $object->getOperator()->shouldBeCalled()->willReturn('LIKE');
        $object->getValue()->shouldBeCalled()->willReturn('foo');
        $object->getLocale()->shouldBeCalled()->willReturn('en_US');
        $object->getScope()->shouldBeCalled()->willReturn('mobile');

        $this->normalize($object)->shouldReturn(
            [
                'field'  => 'name',
                'operator' => 'LIKE',
                'value' => 'foo',
                'locale' => 'en_US',
                'scope' => 'mobile',
            ]
        );
    }

    function it_supports_normalization(ProductConditionInterface $object)
    {
        $this->supportsNormalization($object)->shouldReturn(true);
    }

    function it_does_not_support_normalization_for_invalid_object(AttributeInterface $object)
    {
        $this->supportsNormalization($object)->shouldReturn(false);
    }

    function it_denormalizes()
    {
        $data = [
            'field'  => 'name',
            'operator' => 'LIKE',
            'value' => 'foo',
            'locale' => 'en_US',
            'scope' => 'mobile',
        ];

        $this->denormalize($data, Argument::any())
            ->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition');
    }

    function it_supports_denormalization()
    {
        $type = 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $this->supportsDenormalization(Argument::any(), 'foo')->shouldReturn(false);
    }
}
