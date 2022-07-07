<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\ValueObject\EmailContent;
use Akeneo\SupplierPortal\Supplier\Infrastructure\SetUpPasswordUrl;
use Twig\Environment;

class BuildTwigWelcomeEmail implements BuildWelcomeEmail
{
    public function __construct(
        private Environment $twig,
        private string $domain,
    ) {
    }

    public function __invoke(string $accessToken, string $email): EmailContent
    {
        $setUpPasswordUrl = sprintf(SetUpPasswordUrl::VALUE, $this->domain, $accessToken);

        $htmlContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-invitation.html.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-invitation.txt.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        return new EmailContent($htmlContent, $textContent);
    }
}
