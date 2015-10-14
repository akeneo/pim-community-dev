<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;

class CopyValueActionDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('\PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\CopyValueActionDenormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
    }

    function it_denormalizes()
    {
        $data['type'] = ProductCopyValueActionInterface::ACTION_TYPE;

        $this->denormalize($data, 'copy_value')
            ->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction');
    }

    function it_supports_denormalization()
    {
        $data['type'] = ProductCopyValueActionInterface::ACTION_TYPE;
        $type = 'copy_value';

        $this->supportsDenormalization($data, $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $data['type'] = 'another_action_type';
        $type = 'copy_value';

        $this->supportsDenormalization($data, $type)->shouldReturn(false);
    }
}
