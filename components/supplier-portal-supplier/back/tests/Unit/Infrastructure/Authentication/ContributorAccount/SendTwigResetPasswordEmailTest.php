<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\SendTwigResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SendSwiftmailerEmail;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class SendTwigResetPasswordEmailTest extends TestCase
{
    /** @test */
    public function itBuildsAResetPasswordEmail(): void
    {
        $contributorEmail = 'test@example.com';
        $domain = 'http://wwww.example.com';

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->exactly(2))
            ->method('render')
            ->willReturnOnConsecutiveCalls(
                'htmlContent',
                'textContent',
            );

        $sendEmail = $this->createMock(SendSwiftmailerEmail::class);
        $sendEmail->expects($this->once())->method('__invoke');

        $sut = new SendTwigResetPasswordEmail($sendEmail, $twig, $domain, '/assets');
        ($sut)($contributorEmail, 'foo');
    }
}
