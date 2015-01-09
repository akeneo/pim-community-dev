<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionValueNormalizer;
use Prophecy\Argument;

class SetValueActionNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule\SetValueActionNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes(ProductSetValueActionInterface $object)
    {
        $object->getField()->shouldBeCalled()->willReturn('description');
        $object->getValue()->shouldBeCalled()->willReturn('My beautiful description');
        $object->getLocale()->shouldBeCalled()->willReturn('fr_FR');
        $object->getScope()->shouldBeCalled()->willReturn('mobile');

        $this->normalize($object)->shouldReturn(
            [
                'type'  => ProductSetValueActionInterface::TYPE,
                'field' => 'description',
                'value' => 'My beautiful description',
                'locale'=> 'fr_FR',
                'scope' => 'mobile',
            ]
        );
    }

    function it_supports_normalization(ProductSetValueActionInterface $data)
    {
        $this->supportsNormalization($data)->shouldReturn(true);
    }

    function it_does_not_support_normalization(AttributeInterface $data)
    {
        $this->supportsNormalization($data)->shouldReturn(false);
    }
}
