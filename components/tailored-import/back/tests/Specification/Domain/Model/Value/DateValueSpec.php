<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\TailoredImport\Domain\Model\Value;

use Akeneo\Platform\TailoredImport\Domain\Model\Value\DateValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;
use PhpSpec\ObjectBehavior;

class DateValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(\DateTimeImmutable::createFromFormat('Y-m-d', '2022-08-01'));
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DateValue::class);
    }

    public function it_implements_value_interface()
    {
        $this->shouldBeAnInstanceOf(ValueInterface::class);
    }

    public function it_returns_value()
    {
        $this->getValue()->shouldBeLike(\DateTimeImmutable::createFromFormat('Y-m-d', '2022-08-01'));
    }

    public function it_normalizes()
    {
        $this->normalize()->shouldReturn([
            'type' => 'date',
            'value' => '2022-08-01',
        ]);
    }
}
