<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\BuildWelcomeEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\EmailContent;
use Twig\Environment;

class BuildTwigWelcomeEmail implements BuildWelcomeEmail
{
    private const SET_UP_PASSWORD_URL = '%s/onboarder/supplier/index.html#/set-up-password/%s';

    public function __construct(
        private Environment $twig,
        private string $domain,
    ) {
    }

    public function __invoke(string $accessToken, string $email): EmailContent
    {
        $setUpPasswordUrl = sprintf(self::SET_UP_PASSWORD_URL, $this->domain, $accessToken);

        $htmlContent = $this->twig->render(
            '@AkeneoOnboarderSerenitySupplier/Email/contributor-invitation.html.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoOnboarderSerenitySupplier/Email/contributor-invitation.txt.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        return new EmailContent($htmlContent, $textContent);
    }
}
