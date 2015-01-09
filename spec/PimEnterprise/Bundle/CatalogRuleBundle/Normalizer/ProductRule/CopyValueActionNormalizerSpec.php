<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use Prophecy\Argument;

class CopyValueActionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule\CopyValueActionNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes(ProductCopyValueActionInterface $object)
    {
        $object->getFromField()->shouldBeCalled()->willReturn('description');
        $object->getToField()->shouldBeCalled()->willReturn('description');
        $object->getFromLocale()->shouldBeCalled()->willReturn('fr_FR');
        $object->getToLocale()->shouldBeCalled()->willReturn('en_US');
        $object->getFromScope()->shouldBeCalled()->willReturn('mobile');
        $object->getToScope()->shouldBeCalled()->willReturn('ecommerce');

        $this->normalize($object)->shouldReturn(
            [
                'type'        => ProductCopyValueActionInterface::TYPE,
                'from_field'  => 'description',
                'to_field'    => 'description',
                'from_locale' => 'fr_FR',
                'to_locale'   => 'en_US',
                'from_scope'  => 'mobile',
                'to_scope'    => 'ecommerce'
            ]
        );
    }

    function it_supports_normalization(ProductCopyValueActionInterface $object)
    {
        $this->supportsNormalization($object)->shouldReturn(true);
    }

    function it_does_not_support_normalization_for_invalid_object(AttributeInterface $object)
    {
        $this->supportsNormalization($object)->shouldReturn(false);
    }
}
