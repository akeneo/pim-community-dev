<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\ValueObject;

use PHPUnit\Framework\TestCase;

final class CodeTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateASupplierCodeIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The supplier code cannot be empty.'));

        \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('');
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItExceedsTheMaxLength(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier code is too long. It should have %d characters or less.', 200),
            ),
        );

        \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString(str_repeat('a', 201));
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItContainsForbiddenCharacters(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                'The supplier code can only contain lowercase letters, numbers and underscores.',
            ),
        );

        \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('$uppli€rCØde');
    }

    /** @test */
    public function itCreatesAndGetsASupplierCodeIfItsValid(): void
    {
        $code = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('valid_supplier_code');

        static::assertInstanceOf(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::class, $code);
        static::assertSame('valid_supplier_code', (string) $code);
    }

    /** @test */
    public function itTrimsExtraWhitespaces(): void
    {
        $code = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('valid_supplier_code_with_extra_whitespace ');

        static::assertInstanceOf(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::class, $code);
        static::assertSame('valid_supplier_code_with_extra_whitespace', (string) $code);
    }

    /** @test */
    public function itLowersUpperCases(): void
    {
        $code = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::fromString('SUPPLIER_CODE');

        static::assertInstanceOf(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code::class, $code);
        static::assertSame('supplier_code', (string) $code);
    }
}
