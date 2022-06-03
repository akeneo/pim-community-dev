<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount\SendWelcomeEmailHandler;
use Akeneo\OnboarderSerenity\Domain\Authentication\ContributorAccount\Write\BuildWelcomeEmail;
use Akeneo\OnboarderSerenity\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\Email;
use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\EmailContent;
use PHPUnit\Framework\TestCase;

class SendWelcomeEmailHandlerTest extends TestCase
{
    /** @test */
    public function itSendsAWelcomeEmail(): void
    {
        $contributorEmail = 'jeanjacques@example.com';

        $buildWelcomeEmail = $this->createMock(BuildWelcomeEmail::class);
        $buildWelcomeEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new EmailContent('htmlContent', 'textContent'));

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with(new Email(
                "You've received an invitation to contribute to onboarder",
                'htmlContent',
                'textContent',
                'noreply@akeneo.com',
                $contributorEmail,
            ));

        $sut = new SendWelcomeEmailHandler($sendEmail, $buildWelcomeEmail);
        ($sut)(new SendWelcomeEmail('access-token', $contributorEmail));
    }
}
