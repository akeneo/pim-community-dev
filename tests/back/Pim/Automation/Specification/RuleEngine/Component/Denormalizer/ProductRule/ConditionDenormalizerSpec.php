<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConditionDenormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Pim\Automation\RuleEngine\Component\Denormalizer\ProductRule\ConditionDenormalizer');
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
            ->shouldHaveType('Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition');
    }

    function it_supports_denormalization()
    {
        $type = 'Akeneo\Pim\Automation\RuleEngine\Component\Model\ProductCondition';

        $this->supportsDenormalization(Argument::any(), $type)->shouldReturn(true);
    }

    function it_does_not_support_denormalization_for_invalid_data()
    {
        $this->supportsDenormalization(Argument::any(), 'foo')->shouldReturn(false);
    }
}
