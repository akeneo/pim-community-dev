<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use PHPUnit\Framework\TestCase;

final class CodeTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateASupplierCodeIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The supplier code cannot be empty.'));

        Supplier\ValueObject\Code::fromString('');
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItExceedsTheMaxLength(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier code is too long. It should have %d characters or less.', 200)
            )
        );

        Supplier\ValueObject\Code::fromString(str_repeat('a', 201));
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItContainsForbiddenCharacters(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                'The supplier code can only contain lowercase letters, numbers and underscores.'
            )
        );

        Supplier\ValueObject\Code::fromString('$uppli€rCØde');
    }

    /** @test */
    public function itCreatesAndGetsASupplierCodeIfItsValid(): void
    {
        $code = Supplier\ValueObject\Code::fromString('valid_supplier_code');

        static::assertInstanceOf(Supplier\ValueObject\Code::class, $code);
        static::assertSame('valid_supplier_code', (string) $code);
    }

    /** @test */
    public function itTrimsExtraWhitespaces(): void
    {
        $code = Supplier\ValueObject\Code::fromString('valid_supplier_code_with_extra_whitespace ');

        static::assertInstanceOf(Supplier\ValueObject\Code::class, $code);
        static::assertSame('valid_supplier_code_with_extra_whitespace', (string) $code);
    }

    /** @test */
    public function itLowersUpperCases(): void
    {
        $code = Supplier\ValueObject\Code::fromString('SUPPLIER_CODE');

        static::assertInstanceOf(Supplier\ValueObject\Code::class, $code);
        static::assertSame('supplier_code', (string) $code);
    }
}
