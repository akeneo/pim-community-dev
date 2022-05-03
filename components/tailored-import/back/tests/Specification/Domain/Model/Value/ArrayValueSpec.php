<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\ArrayValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class ArrayValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(['a_value']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ArrayValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_value()
    {
        $this->getValue()->shouldReturn(['a_value']);
    }
}
