<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\SendResetPasswordEmailOnPasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendResetPasswordEmail;
use PHPUnit\Framework\TestCase;

final class SendResetPasswordEmailOnPasswordResetTest extends TestCase
{
    /** @test */
    public function itSubscribesToPasswordResetEvent(): void
    {
        $this->assertSame(
            [PasswordReset::class],
            array_keys(SendResetPasswordEmailOnPasswordReset::getSubscribedEvents()),
        );
    }

    /** @test */
    public function itCallsTheSendResetPasswordEmailService(): void
    {
        $event = new PasswordReset('test@example.com', 'foo');

        $sendResetPasswordEmail = $this->createMock(SendResetPasswordEmail::class);
        $sendResetPasswordEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with('test@example.com', 'foo');

        $sut = new SendResetPasswordEmailOnPasswordReset($sendResetPasswordEmail);

        $sut->sendResetPasswordEmail($event);
    }
}
