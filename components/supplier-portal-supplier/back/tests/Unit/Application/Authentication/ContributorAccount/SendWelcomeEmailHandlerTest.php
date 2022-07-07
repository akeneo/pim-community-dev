<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\EmailContent;
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
