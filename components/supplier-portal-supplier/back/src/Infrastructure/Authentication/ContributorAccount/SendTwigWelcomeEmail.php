<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendWelcomeEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SendSwiftmailerEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SwiftEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\SetUpPasswordUrl;
use Twig\Environment;

class SendTwigWelcomeEmail implements SendWelcomeEmail
{
    public function __construct(
        private SendSwiftmailerEmail $sendEmail,
        private Environment $twig,
        private string $domain,
        private string $assetsPath,
    ) {
    }

    public function __invoke(string $email, string $accessToken): void
    {
        $setUpPasswordUrl = sprintf(SetUpPasswordUrl::VALUE, $this->domain, $accessToken);

        $embeddedLogo = \Swift_Image::fromPath(sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png'));

        $htmlContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-invitation.html.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
                'logoCID' => $embeddedLogo->getId(),
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-invitation.txt.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
        );

        ($this->sendEmail)(
            new SwiftEmail(
                "You've received an invitation to contribute to Akeneo Supplier Portal",
                $htmlContent,
                $textContent,
                'noreply@akeneo.com',
                $email,
                [$embeddedLogo],
            )
        );
    }
}
