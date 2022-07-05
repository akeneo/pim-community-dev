<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use PHPUnit\Framework\TestCase;

final class SendResetPasswordEmailTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheContributorAccountEmailAndItsAccessToken(): void
    {
        $resetPasswordRequestedReflectionClass = new \ReflectionClass(SendResetPasswordEmail::class);
        $properties = $resetPasswordRequestedReflectionClass->getProperties();

        static::assertCount(2, $properties);
        static::assertSame(
            'email',
            $properties[0]->getName(),
        );
        static::assertSame(
            'accessToken',
            $properties[1]->getName(),
        );
    }
}
