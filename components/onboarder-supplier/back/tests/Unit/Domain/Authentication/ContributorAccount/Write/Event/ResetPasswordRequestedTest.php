<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Test\Unit\Domain\Authentication\ContributorAccount\Write\Event;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event\ResetPasswordRequested;
use PHPUnit\Framework\TestCase;

final class ResetPasswordRequestedTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheContributorAccountEmailAndItsAccessToken(): void
    {
        $resetPasswordRequestedReflectionClass = new \ReflectionClass(ResetPasswordRequested::class);
        $properties = $resetPasswordRequestedReflectionClass->getProperties();

        static::assertCount(2, $properties);
        static::assertSame(
            'contributorAccountEmail',
            $properties[0]->getName(),
        );
        static::assertSame(
            'accessToken',
            $properties[1]->getName(),
        );
    }
}
