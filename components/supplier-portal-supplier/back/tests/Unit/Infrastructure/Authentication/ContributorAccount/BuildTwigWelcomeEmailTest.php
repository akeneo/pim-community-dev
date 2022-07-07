<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\BuildTwigWelcomeEmail;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class BuildTwigWelcomeEmailTest extends TestCase
{
    /** @test */
    public function itBuildsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';
        $domain = 'http://wwww.example.com';

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->exactly(2))
            ->method('render')
            ->withConsecutive(
                [
                    '@AkeneoSupplierPortalSupplier/Email/contributor-invitation.html.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com/supplier-portal/index.html#/set-up-password/foo',
                    ],
                ],
                [
                    '@AkeneoSupplierPortalSupplier/Email/contributor-invitation.txt.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com/supplier-portal/index.html#/set-up-password/foo',
                    ],
                ],
            )
            ->willReturnOnConsecutiveCalls(
                'htmlContent',
                'textContent',
            );

        $sut = new BuildTwigWelcomeEmail($twig, $domain);
        $email = ($sut)($contributorEmail, 'foo');

        static::assertSame("You've received an invitation to contribute to Akeneo Supplier Portal", $email->subject);
        static::assertSame('htmlContent', $email->htmlContent);
        static::assertSame('textContent', $email->txtContent);
        static::assertSame('noreply@akeneo.com', $email->from);
        static::assertSame($contributorEmail, $email->to);
    }
}
