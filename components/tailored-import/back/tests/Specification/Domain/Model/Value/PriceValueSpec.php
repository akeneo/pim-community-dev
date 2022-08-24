<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\PriceValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class PriceValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('123', 'EUR');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PriceValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_value()
    {
        $this->getValue()->shouldReturn('123');
    }

    public function it_returns_currency()
    {
        $this->getCurrency()->shouldReturn('EUR');
    }

    public function it_normalizes()
    {
        $this->normalize()->shouldReturn([
            'type' => 'price',
            'value' => '123',
            'currency' => 'EUR',
        ]);
    }

    public function it_does_not_support_empty_value()
    {
        $this->beConstructedWith('', 'EUR');
        $this->shouldThrow(new \InvalidArgumentException('Expected a different value than "".'))->duringInstantiation();
    }

    public function it_does_not_support_empty_currency_code()
    {
        $this->beConstructedWith('123', '');
        $this->shouldThrow(new \InvalidArgumentException('Expected a different value than "".'))->duringInstantiation();
    }
}
