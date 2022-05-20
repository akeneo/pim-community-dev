<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\Email;
use Symfony\Component\Routing\RouterInterface;

final class SendWelcomeEmailHandler
{
    public function __construct(private SendEmail $sendEmail, private RouterInterface $router)
    {
    }

    public function __invoke(SendWelcomeEmail $command): void
    {
        $linkWithToken = $this->router->generate(
            'onboarder_serenity_contributor_set_up_password',
            ['token' => (string) $command->contributorAccount->accessToken()]
        );

        $email = new Email(
            'Welcome', //To define
            $linkWithToken,
            $linkWithToken,
            'no-reply@akeneo.com', //To define
            (string) $command->contributorAccount->email(),
        );
        ($this->sendEmail)($email);
    }
}
