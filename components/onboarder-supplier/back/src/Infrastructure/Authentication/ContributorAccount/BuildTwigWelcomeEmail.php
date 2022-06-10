<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\BuildWelcomeEmail;
use Akeneo\OnboarderSerenity\Supplier\Domain\Mailer\ValueObject\EmailContent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class BuildTwigWelcomeEmail implements BuildWelcomeEmail
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
    ) {
    }

    public function __invoke(string $accessToken, string $email): EmailContent
    {
        $setUpPasswordUrl = $this->urlGenerator->generate(
            'onboarder_serenity_supplier_contributor_set_up_password',
            ['token' => $accessToken],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

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
