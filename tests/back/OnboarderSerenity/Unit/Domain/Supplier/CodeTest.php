<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Code;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CodeTest extends KernelTestCase
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
                sprintf('The supplier code is too long. It should have %d characters or less.', 200)
            )
        );

        Code::fromString(str_repeat('a', 201));
    }

    /** @test */
    public function itDoesNotCreateASupplierCodeIfItContainsForbiddenCharacters(): void
    {
        static::expectExceptionObject(
            new \InvalidArgumentException(
                'The supplier code can only contain lowercase letters, numbers and underscores.'
            )
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
}
