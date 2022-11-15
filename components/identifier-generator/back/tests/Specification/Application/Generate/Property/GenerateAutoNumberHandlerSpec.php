<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use PhpSpec\ObjectBehavior;

class GenerateAutoNumberHandlerSpec extends ObjectBehavior
{
    public function let(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $this->beConstructedWith($getNextIdentifierQuery);
    }

    public function it_should_support_only_auto_numbers(): void
    {
        $this->getPropertyClass()->shouldReturn(AutoNumber::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_auto_number(): void
    {
        $target = Target::fromString('sku');
        $freeText = FreeText::fromNormalized([
            'type' => FreeText::type(),
            'string' => 'AKN-',
        ]);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [$freeText, $target, 'AKN-']);
    }

    public function it_should_return_next_number(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);
        $getNextIdentifierQuery->fromPrefix($target, 'AKN-')
            ->shouldBeCalled()
            ->willReturn(42);

        $this->__invoke($autoNumber, $target, 'AKN-')->shouldReturn('42');
    }

    public function it_should_set_min_number(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 50,
            'digitsMin' => 1,
        ]);
        $getNextIdentifierQuery->fromPrefix($target, 'AKN-')
            ->shouldBeCalled()
            ->willReturn(42);

        $this->__invoke($autoNumber, $target, 'AKN-')->shouldReturn('50');
    }

    public function it_should_add_digits_when_number_is_too_low(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 5,
        ]);
        $getNextIdentifierQuery->fromPrefix($target, 'AKN-')
            ->shouldBeCalled()
            ->willReturn(42);

        $this->__invoke($autoNumber, $target, 'AKN-')->shouldReturn('00042');
    }

    public function it_should_not_add_digits_when_number_is_too_high(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 5,
        ]);
        $getNextIdentifierQuery->fromPrefix($target, 'AKN-')
            ->shouldBeCalled()
            ->willReturn(426942);

        $this->__invoke($autoNumber, $target, 'AKN-')->shouldReturn('426942');
    }
}
