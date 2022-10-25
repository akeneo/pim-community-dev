<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendResetPasswordEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SendSymfonyEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SymfonyEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\SetUpPasswordUrl;

final class SendTwigResetPasswordEmail implements SendResetPasswordEmail
{
    public function __construct(
        private SendSymfonyEmail $sendSymfonyEmail,
        private string $domain,
        private string $assetsPath,
    ) {
    }

    public function __invoke(string $email, string $accessToken): void
    {
        $setUpPasswordUrl = sprintf(SetUpPasswordUrl::VALUE, $this->domain, $accessToken);
        $embeddedLogoPath = sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png');

        $symfonyEmail = new SymfonyEmail(
            'Reset your password',
            '@AkeneoSupplierPortalSupplier/Email/contributor-reset-password.html.twig',
            '@AkeneoSupplierPortalSupplier/Email/contributor-reset-password.txt.twig',
            [
                'contributorEmail' => $email,
                'url' => $setUpPasswordUrl,
            ],
            $email,
            $embeddedLogoPath,
        );

        ($this->sendSymfonyEmail)($symfonyEmail);
    }
}
