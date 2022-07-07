<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\EmailContent;
use PHPUnit\Framework\TestCase;

final class SendResetPasswordEmailHandlerTest extends TestCase
{
    /** @test */
    public function itSendsAResetPasswordEmail(): void
    {
        $contributorAccountEmail = 'test@example.com';

        $buildResetPasswordEmail = $this->createMock(BuildResetPasswordEmail::class);
        $buildResetPasswordEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new EmailContent('htmlContent', 'textContent'));

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with(new Email(
                'Reset your password',
                'htmlContent',
                'textContent',
                'noreply@akeneo.com',
                $contributorAccountEmail,
            ));

        $sut = new SendResetPasswordEmailHandler($sendEmail, $buildResetPasswordEmail);
        ($sut)(new SendResetPasswordEmail($contributorAccountEmail, 'foo'));
    }
}
