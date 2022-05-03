<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class StringValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('a_string_value');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(StringValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_value()
    {
        $this->getValue()->shouldReturn('a_string_value');
    }

    public function it_does_not_support_empty_string_value()
    {
        $this->beConstructedWith('');
        $this->shouldThrow(new \InvalidArgumentException('Expected a different value than "".'))->duringInstantiation();
    }
}
