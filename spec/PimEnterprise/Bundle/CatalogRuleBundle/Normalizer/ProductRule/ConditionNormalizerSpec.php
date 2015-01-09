<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use Prophecy\Argument;

class ConditionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule\ConditionNormalizer');
    }

    function it_implements()
    {
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
}
