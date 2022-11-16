<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendGoodbyeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Email;
use Akeneo\SupplierPortal\Supplier\Domain\SendEmail;

class SendTwigGoodbyeEmail implements SendGoodbyeEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private string $assetsPath,
    ) {
    }

    public function __invoke(string $email): void
    {
        $embeddedLogoPath = sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png');

        $email = new Email(
            'Your account has been removed from Akeneo Supplier Portal',
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.html.twig',
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.txt.twig',
            [
                'contributorEmail' => $email,
            ],
            $email,
            $embeddedLogoPath,
        );

        ($this->sendEmail)($email);
    }
}
