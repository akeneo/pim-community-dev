<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use PHPUnit\Framework\TestCase;

final class LabelTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateASupplierLabelIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The supplier label cannot be empty.'));

        Supplier\Label::fromString('');
    }

    /** @test */
    public function itDoesNotCreateASupplierLabelIfItExceedsTheMaxLength(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier label is too long. It should have %d characters or less.', 200)
            )
        );

        Supplier\Label::fromString(str_repeat('a', 201));
    }

    /** @test */
    public function itCreatesAndGetsASupplierLabelIfItsValid(): void
    {
        $label = Supplier\Label::fromString('A valid supplier label');

        static::assertInstanceOf(Supplier\Label::class, $label);
        static::assertSame('A valid supplier label', (string) $label);
    }
}
