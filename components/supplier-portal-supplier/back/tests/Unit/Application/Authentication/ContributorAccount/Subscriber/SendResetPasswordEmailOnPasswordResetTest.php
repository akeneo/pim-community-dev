<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Subscriber\SendResetPasswordEmailOnPasswordReset;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Event\PasswordReset;
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
    public function itCallsTheSendResetPasswordEmailHandler(): void
    {
        $event = new PasswordReset('test@example.com', 'foo');

        $sendResetPasswordEmailHandler = $this->createMock(SendResetPasswordEmailHandler::class);
        $sendResetPasswordEmailHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new SendResetPasswordEmail('test@example.com', 'foo'));

        $sut = new SendResetPasswordEmailOnPasswordReset($sendResetPasswordEmailHandler);

        $sut->sendResetPasswordEmail($event);
    }
}
