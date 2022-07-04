<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Domain\Mailer\ValueObject\EmailContent;
use Akeneo\SupplierPortal\Infrastructure\SetUpPasswordUrl;
use Twig\Environment;

final class BuildTwigResetPasswordEmail implements BuildResetPasswordEmail
{
    public function __construct(
        private Environment $twig,
        private string $domain,
    ) {
    }

    public function __invoke(string $email, string $accessToken): EmailContent
    {
        $setUpPasswordUrl = sprintf(SetUpPasswordUrl::VALUE, $this->domain, $accessToken);

        $htmlContent = $this->twig->render(
            '@AkeneoSupplierPortal/Email/contributor-reset-password.html.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoSupplierPortal/Email/contributor-reset-password.txt.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        return new EmailContent($htmlContent, $textContent);
    }
}
