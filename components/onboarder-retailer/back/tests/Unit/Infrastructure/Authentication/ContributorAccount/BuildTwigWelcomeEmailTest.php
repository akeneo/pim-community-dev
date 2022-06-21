<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Infrastructure\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\BuildTwigWelcomeEmail;
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
                    '@AkeneoOnboarderSerenityRetailer/Email/contributor-invitation.html.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com/onboarder/supplier/index.html#/set-up-password/foo',
                    ],
                ],
                [
                    '@AkeneoOnboarderSerenityRetailer/Email/contributor-invitation.txt.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com/onboarder/supplier/index.html#/set-up-password/foo',
                    ],
                ],
            )
            ->willReturnOnConsecutiveCalls(
                'htmlContent',
                'textContent',
            );

        $sut = new BuildTwigWelcomeEmail($twig, $domain);
        $emailContent = ($sut)('foo', $contributorEmail);

        static::assertSame('htmlContent', $emailContent->htmlContent);
        static::assertSame('textContent', $emailContent->textContent);
    }
}
