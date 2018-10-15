<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule;

use Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule\ConditionDenormalizer;
use Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConditionDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(ProductCondition::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ConditionDenormalizer::class);
    }

    function it_implements()
    {
        $this->shouldHaveType(DenormalizerInterface::class);
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
            ->shouldHaveType(ProductCondition::class);
    }

    function it_supports_denormalization()
    {
        $type = ProductCondition::class;

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $this->supportsDenormalization(Argument::any(), 'foo')->shouldReturn(false);
    }
}
