<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Akeneo\SupplierPortal\Supplier\Infrastructure\SetUpPasswordUrl;
use Twig\Environment;

final class BuildTwigResetPasswordEmail implements BuildResetPasswordEmail
{
    public function __construct(
        private Environment $twig,
        private string $domain,
        private string $assetsPath,
    ) {
    }

    public function __invoke(string $email, string $accessToken): Email
    {
        $setUpPasswordUrl = sprintf(SetUpPasswordUrl::VALUE, $this->domain, $accessToken);

        $embededLogo = \Swift_Image::fromPath(sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png'));

        $htmlContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-reset-password.html.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
                'logoCID' => $embededLogo->getId(),
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-reset-password.txt.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        return new Email(
            'Reset your password',
            $htmlContent,
            $textContent,
            'noreply@akeneo.com',
            $email,
            [$embededLogo],
        );
    }
}
