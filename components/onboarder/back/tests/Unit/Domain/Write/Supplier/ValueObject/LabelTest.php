<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\ValueObject;

use PHPUnit\Framework\TestCase;

final class LabelTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateASupplierLabelIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The supplier label cannot be empty.'));

        \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Label::fromString('');
    }

    /** @test */
    public function itDoesNotCreateASupplierLabelIfItExceedsTheMaxLength(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier label is too long. It should have %d characters or less.', 200),
            ),
        );

        \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Label::fromString(str_repeat('a', 201));
    }

    /** @test */
    public function itCreatesAndGetsASupplierLabelIfItsValid(): void
    {
        $label = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Label::fromString('A valid supplier label');

        static::assertInstanceOf(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Label::class, $label);
        static::assertSame('A valid supplier label', (string) $label);
    }
}
