<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\NullValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class NullValueSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(NullValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_value()
    {
        $this->getValue()->shouldReturn(null);
    }
}
