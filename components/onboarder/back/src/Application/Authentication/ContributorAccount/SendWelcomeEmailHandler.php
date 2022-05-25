<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Domain\Mailer\SendEmail;
use Akeneo\OnboarderSerenity\Domain\Mailer\ValueObject\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class SendWelcomeEmailHandler
{
    public function __construct(
        private SendEmail $sendEmail,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
    ) {
    }

    public function __invoke(SendWelcomeEmail $command): void
    {
        $setUpPasswordUrl = $this->urlGenerator->generate(
            'onboarder_serenity_contributor_set_up_password',
            ['token' => $command->accessToken],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $htmlContent = $this->twig->render(
            '@AkeneoOnboarderSerenity/Email/contributor-invitation.html.twig',
            [
                'contributorEmail' => $command->email,
                'url' => $setUpPasswordUrl,
            ],
        );

        $email = new Email(
            'Welcome', //To define
            $htmlContent,
            $setUpPasswordUrl,
            'no-reply@akeneo.com', //To define
            $command->email,
        );
        ($this->sendEmail)($email);
    }
}
