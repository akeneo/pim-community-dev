<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Infrastructure\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\BuildTwigWelcomeEmail;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class BuildTwigWelcomeEmailTest extends TestCase
{
    /** @test */
    public function itBuildsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                'onboarder_serenity_contributor_set_up_password',
                ['token' => 'foo'],
                UrlGeneratorInterface::ABSOLUTE_URL,
            )
            ->willReturn('http://wwww.example.com');

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->exactly(2))
            ->method('render')
            ->withConsecutive(
                [
                    '@AkeneoOnboarderSerenity/Email/contributor-invitation.html.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com',
                    ],
                ],
                [
                    '@AkeneoOnboarderSerenity/Email/contributor-invitation.txt.twig',
                    [
                        'contributorEmail' => $contributorEmail,
                        'url' => 'http://wwww.example.com',
                    ],
                ],
            )
            ->willReturnOnConsecutiveCalls(
                'htmlContent',
                'textContent',
            );

        $sut = new BuildTwigWelcomeEmail($urlGenerator, $twig);
        $emailContent = ($sut)('foo', $contributorEmail);

        static::assertSame('htmlContent', $emailContent->htmlContent);
        static::assertSame('textContent', $emailContent->textContent);
    }
}
