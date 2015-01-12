<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConditionDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule\ConditionDenormalizer');
    }

    function it_implements()
    {
        $this->shouldHaveType('Symfony\Component\Serializer\Normalizer\DenormalizerInterface');
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
