<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildGoodbyeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendGoodbyeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\SendEmail;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SendGoodbyeEmailTest extends TestCase
{
    /** @test */
    public function itSendsAGoodbyeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';
        $email = new Email(
            'Goodbye email',
            'htmlContent',
            'textContent',
            'noreply@akeneo.com',
            $contributorEmail,
        );

        $buildGoodbyeEmail = $this->createMock(BuildGoodbyeEmail::class);
        $buildGoodbyeEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn($email);

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with($email);

        $sut = new SendGoodbyeEmail($sendEmail, $buildGoodbyeEmail, new NullLogger());
        ($sut)($contributorEmail);
    }
}
