<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class InvalidValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('an_error_key');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(InvalidValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_error_key()
    {
        $this->getErrorKey()->shouldReturn('an_error_key');
    }

    public function it_throws_an_exception_when_trying_to_access_to_value()
    {
        $this->shouldThrow(new \RuntimeException('You can\'t access to value on an InvalidValue object'))
            ->during('getValue');
    }

    public function it_normalizes()
    {
        $this->normalize()->shouldReturn([
            'type' => 'invalid',
            'error_key' => 'an_error_key',
        ]);
    }
}
