<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SendWelcomeEmailTest extends TestCase
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

        $sut = new SendWelcomeEmail($sendEmail, $buildWelcomeEmail, new NullLogger());
        ($sut)($contributorEmail, 'access-token');
    }
}
