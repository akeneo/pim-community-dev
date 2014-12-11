<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionValueNormalizer;
use Prophecy\Argument;

class ProductSetValueActionNormalizerSpec extends ObjectBehavior
{
    public function let(ProductSetValueActionValueNormalizer $valueNormalizer)
    {
        $this->beConstructedWith('\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction', $valueNormalizer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_normalizes(ProductSetValueActionInterface $object, $valueNormalizer)
    {
        $object->getField()->shouldBeCalled()->willReturn('description');
        $object->getValue()->shouldBeCalled()->willReturn('My beautiful description');
        $object->getLocale()->shouldBeCalled()->willReturn('fr_FR');
        $object->getScope()->shouldBeCalled()->willReturn('mobile');

        $valueNormalizer
            ->normalize('My beautiful description', 'array_updater', ['attribute_code' => 'description'])
            ->willReturn('My beautiful description');

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

    function it_denormalizes()
    {
        $data['type'] = ProductSetValueActionInterface::TYPE;

        $this->denormalize($data, 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction')
            ->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction');
    }

    function it_supports_denormalization()
    {
        $data['type'] = ProductSetValueActionInterface::TYPE;
        $type = '\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction';

        $this->supportsDenormalization($data, $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_wrong_object()
    {
        $data['type'] = ProductSetValueActionInterface::TYPE;
        $type = '\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition';

        $this->supportsDenormalization($data, $type)->shouldReturn(false);
    }
}
