<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\Model;
use Akeneo\OnboarderSerenity\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\Email;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class SendWelcomeEmailHandlerTest extends TestCase
{
    /** @test */
    public function itSendsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('http://wwww.example.com');

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->willReturn('htmlContent');

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with(new Email('Welcome', 'htmlContent', 'http://wwww.example.com', 'no-reply@akeneo.com', $contributorEmail));

        $sut = new SendWelcomeEmailHandler($sendEmail, $urlGenerator, $twig);
        ($sut)(new SendWelcomeEmail(Model\ContributorAccount::fromEmail($contributorEmail)));
    }
}
