<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Test\Unit\Application\Authentication\ContributorAccount\Subscriber;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\Subscriber\SendResetPasswordEmailOnPasswordResetRequested;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\Write\Event\ResetPasswordRequested;
use PHPUnit\Framework\TestCase;

final class SendResetPasswordEmailOnPasswordResetRequestedTest extends TestCase
{
    /** @test */
    public function itSubscribesToResetPasswordRequestedEvent(): void
    {
        $this->assertSame(
            [ResetPasswordRequested::class],
            array_keys(SendResetPasswordEmailOnPasswordResetRequested::getSubscribedEvents()),
        );
    }

    /** @test */
    public function itCallsTheSendResetPasswordEmailHandler(): void
    {
        $event = new ResetPasswordRequested('test@example.com', 'foo');

        $sendResetPasswordEmailHandler = $this->createMock(SendResetPasswordEmailHandler::class);
        $sendResetPasswordEmailHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new SendResetPasswordEmail('test@example.com', 'foo'));

        $sut = new SendResetPasswordEmailOnPasswordResetRequested($sendResetPasswordEmailHandler);

        $sut->sendResetPasswordEmail($event);
    }
}
