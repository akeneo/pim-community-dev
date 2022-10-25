<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\SendTwigWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SendSymfonyEmail;
use PHPUnit\Framework\TestCase;

final class SendTwigWelcomeEmailTest extends TestCase
{
    /** @test */
    public function itBuildsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';
        $domain = 'http://wwww.example.com';

        $sendEmail = $this->createMock(SendSymfonyEmail::class);
        $sendEmail->expects($this->once())->method('__invoke');

        $sut = new SendTwigWelcomeEmail($sendEmail, $domain, '/assets');
        ($sut)($contributorEmail, 'foo');
    }
}
