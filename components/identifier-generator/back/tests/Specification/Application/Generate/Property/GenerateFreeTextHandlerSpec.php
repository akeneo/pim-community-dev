<?php

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use PhpSpec\ObjectBehavior;

class GenerateFreeTextHandlerSpec extends ObjectBehavior
{
    public function let(): void {
    }

    public function it_should_support_only_auto_numbers(): void
    {
        $this->getPropertyClass()->shouldReturn(FreeText::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_free_text(): void
    {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [$autoNumber, $target, 'AKN-']);
    }

    public function it_should_return_string(): void
    {
        $target = Target::fromString('sku');
        $freeText = FreeText::fromNormalized([
            'type' => FreeText::type(),
            'string' => 'AKN-',
        ]);

        $this->__invoke($freeText, $target, 'AKN-')->shouldReturn('AKN-');
    }
}
