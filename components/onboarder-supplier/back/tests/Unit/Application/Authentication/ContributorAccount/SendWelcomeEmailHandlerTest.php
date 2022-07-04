<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Domain\Mailer\SendEmail;
use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\Email;
use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\EmailContent;
use PHPUnit\Framework\TestCase;

class SendWelcomeEmailHandlerTest extends TestCase
{
    /** @test */
    public function itSendsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';

        $buildWelcomeEmail = $this->createMock(BuildWelcomeEmail::class);
        $buildWelcomeEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new EmailContent('htmlContent', 'textContent'));

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with(new Email(
                'You\'ve received an invitation to contribute to Akeneo Supplier Portal',
                'htmlContent',
                'textContent',
                'noreply@akeneo.com',
                $contributorEmail,
            ));

        $sut = new SendWelcomeEmailHandler($sendEmail, $buildWelcomeEmail);
        ($sut)(new SendWelcomeEmail('access-token', $contributorEmail));
    }
}
