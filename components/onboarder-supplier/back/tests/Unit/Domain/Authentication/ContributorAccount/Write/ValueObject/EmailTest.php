<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Domain\Authentication\ContributorAccount\Write\ValueObject;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    /** @test */
    public function itThrowsAnErrorIfTheFormatIsNotValid(): void
    {
        static::expectExceptionObject(new \InvalidArgumentException('The email must be valid.'));

        Email::fromString('foo@');
    }

    /** @test */
    public function itCreatesAnEmail(): void
    {
        $email = Email::fromString('foo@foo.bar');

        static::assertInstanceOf(Email::class, $email);
        static::assertSame('foo@foo.bar', (string) $email);
    }
}
