<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\Supplier\Write\Model\Supplier;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Code;
use PHPUnit\Framework\TestCase;

final class CodeTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateASupplierCodeIfItsEmpty(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The supplier code cannot be empty.'));

        Code::fromString('');
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItExceedsTheMaxLength(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                sprintf('The supplier code is too long. It should have %d characters or less.', 200),
            ),
        );

        Code::fromString(str_repeat('a', 201));
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItContainsForbiddenCharacters(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                'The supplier code can only contain lowercase letters, numbers and underscores.',
            ),
        );

        Code::fromString('$uppli€rCØde');
    }

    /** @test */
    public function itCreatesAndGetsASupplierCodeIfItsValid(): void
    {
        $code = Code::fromString('valid_supplier_code');

        static::assertInstanceOf(Code::class, $code);
        static::assertSame('valid_supplier_code', (string) $code);
    }

    /** @test */
    public function itTrimsExtraWhitespaces(): void
    {
        $code = Code::fromString('valid_supplier_code_with_extra_whitespace ');

        static::assertInstanceOf(Code::class, $code);
        static::assertSame('valid_supplier_code_with_extra_whitespace', (string) $code);
    }

    /** @test */
    public function itLowersUpperCases(): void
    {
        $code = Code::fromString('SUPPLIER_CODE');

        static::assertInstanceOf(Code::class, $code);
        static::assertSame('supplier_code', (string) $code);
    }
}
