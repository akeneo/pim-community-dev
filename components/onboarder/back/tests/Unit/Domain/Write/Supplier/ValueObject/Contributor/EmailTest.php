<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Domain\Write\Supplier\ValueObject\Contributor;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Contributor\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    /** @test */
    public function itDoesNotCreateAContributorEmailIfTheFormatIsNotValid(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The contributor email must be valid.'));

        Email::fromString('foo@');
    }

    /** @test */
    public function itCreatesAndGetsAContributorEmailIfItsValid(): void
    {
        $email = Email::fromString('foo@foo.bar');

        static::assertInstanceOf(Email::class, $email);
        static::assertSame('foo@foo.bar', (string) $email);
    }
}
