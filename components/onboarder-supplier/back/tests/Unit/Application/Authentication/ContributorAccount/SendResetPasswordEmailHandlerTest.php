<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Test\Unit\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\SendResetPasswordEmailHandler;
use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\Email;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\EmailContent;
use PHPUnit\Framework\TestCase;

final class SendResetPasswordEmailHandlerTest extends TestCase
{
    /** @test */
    public function itSendsAResetPasswordEmail(): void
    {
        $contributorAccountEmail = 'test@example.com';

        $buildResetPasswordEmail = $this->createMock(BuildResetPasswordEmail::class);
        $buildResetPasswordEmail
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn(new EmailContent('htmlContent', 'textContent'));

        $sendEmail = $this->createMock(SendEmail::class);
        $sendEmail
            ->expects($this->once())
            ->method('__invoke')
            ->with(new Email(
                'Reset your password',
                'htmlContent',
                'textContent',
                'noreply@akeneo.com',
                $contributorAccountEmail,
            ));

        $sut = new SendResetPasswordEmailHandler($sendEmail, $buildResetPasswordEmail);
        ($sut)(new SendResetPasswordEmail($contributorAccountEmail, 'foo'));
    }
}
