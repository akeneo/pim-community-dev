<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\MeasurementValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class MeasurementValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('123', 'HERTZ');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MeasurementValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_value()
    {
        $this->getValue()->shouldReturn('123');
    }

    public function it_returns_unit()
    {
        $this->getUnit()->shouldReturn('HERTZ');
    }

    public function it_normalizes()
    {
        $this->normalize()->shouldReturn([
            'type' => 'measurement',
            'value' => '123',
            'unit' => 'HERTZ',
        ]);
    }

    public function it_does_not_support_empty_value()
    {
        $this->beConstructedWith('', 'HERTZ');
        $this->shouldThrow(new \InvalidArgumentException('Expected a different value than "".'))->duringInstantiation();
    }

    public function it_does_not_support_empty_unit()
    {
        $this->beConstructedWith('123', '');
        $this->shouldThrow(new \InvalidArgumentException('Expected a different value than "".'))->duringInstantiation();
    }
}
