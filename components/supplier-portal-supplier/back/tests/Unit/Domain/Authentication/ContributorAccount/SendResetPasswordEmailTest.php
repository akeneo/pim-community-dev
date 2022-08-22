<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class SendResetPasswordEmailTest extends TestCase
{
    /** @test */
    public function itSendsAResetPasswordEmail(): void
    {
        $contributorAccountEmail = 'test@example.com';
        $email = new Email(
            'Reset your password',
            'htmlContent',
            'textContent',
            'noreply@akeneo.com',
            $contributorAccountEmail,
        );

        $buildResetPasswordEmail = $this->createMock(BuildResetPasswordEmail::class);
        $buildResetPasswordEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($email);

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with($email);

        $sut = new SendResetPasswordEmail($sendEmail, $buildResetPasswordEmail, new NullLogger());
        ($sut)($contributorAccountEmail, 'foo');
    }
}
