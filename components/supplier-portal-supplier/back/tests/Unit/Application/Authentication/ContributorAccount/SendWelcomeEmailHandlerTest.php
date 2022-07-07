<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use PHPUnit\Framework\TestCase;

class SendWelcomeEmailHandlerTest extends TestCase
{
    /** @test */
    public function itSendsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';
        $email = new Email(
            'You\'ve received an invitation to contribute to Akeneo Supplier Portal',
            'htmlContent',
            'textContent',
            'noreply@akeneo.com',
            $contributorEmail,
        );

        $buildWelcomeEmail = $this->createMock(BuildWelcomeEmail::class);
        $buildWelcomeEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($email);

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with($email);

        $sut = new SendWelcomeEmailHandler($sendEmail, $buildWelcomeEmail);
        ($sut)(new SendWelcomeEmail($contributorEmail, 'access-token'));
    }
}
