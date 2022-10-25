<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendGoodbyeEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SendSymfonyEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SymfonyEmail;

class SendTwigGoodbyeEmail implements SendGoodbyeEmail
{
    public function __construct(
        private SendSymfonyEmail $sendSymfonyEmail,
        private string $assetsPath,
    ) {
    }

    public function __invoke(string $email): void
    {
        $embeddedLogoPath = sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png');

        $symfonyEmail = new SymfonyEmail(
            'Your account has been removed from Akeneo Supplier Portal',
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.html.twig',
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.txt.twig',
            [
                'contributorEmail' => $email,
            ],
            $email,
            $embeddedLogoPath,
        );

        ($this->sendSymfonyEmail)($symfonyEmail);
    }
}
