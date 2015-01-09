<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductSetValueActionValueNormalizer;
use Prophecy\Argument;

class SetValueActionDenormalizerSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule\SetValueActionNormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
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
