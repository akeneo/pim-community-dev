<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Test\Unit\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\BuildTwigResetPasswordEmail;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

final class BuildTwigResetPasswordEmailTest extends TestCase
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
            ->withConsecutive(
                [
                    '@AkeneoSupplierPortal/Email/contributor-reset-password.html.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com/supplier-portal/index.html#/set-up-password/foo',
                    ],
                ],
                [
                    '@AkeneoSupplierPortal/Email/contributor-reset-password.txt.twig',
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

        $sut = new BuildTwigResetPasswordEmail($twig, $domain);
        $emailContent = ($sut)($contributorEmail, 'foo');

        static::assertSame('htmlContent', $emailContent->htmlContent);
        static::assertSame('textContent', $emailContent->textContent);
    }
}
