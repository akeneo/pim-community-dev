<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\Contributor\ValueObject;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Contributor;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateAContributorEmailIfTheFormatIsNotValid(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The contributor email must be valid.'));

        Contributor\ValueObject\Email::fromString('foo@');
    }

    /** @test */
    public function itCreatesAndGetsAContributorEmailIfItsValid(): void
    {
        $code = Contributor\ValueObject\Email::fromString('foo@foo.bar');

        static::assertInstanceOf(Contributor\ValueObject\Email::class, $code);
        static::assertSame('foo@foo.bar', (string) $code);
    }
}
