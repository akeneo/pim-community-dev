<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\SendGoodbyeEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SendSwiftmailerEmail;
use Akeneo\SupplierPortal\Supplier\Infrastructure\Mailer\SwiftEmail;
use Twig\Environment;

class SendTwigGoodbyeEmail implements SendGoodbyeEmail
{
    public function __construct(private SendSwiftmailerEmail $sendEmail, private Environment $twig, private string $assetsPath)
    {
    }

    public function __invoke(string $email): void
    {
        $embeddedLogo = \Swift_Image::fromPath(sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png'));

        $htmlContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.html.twig',
            [
                'contributorEmail' => $email,
                'logoCID' => $embeddedLogo->getId(),
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.txt.twig',
            [
                'contributorEmail' => $email,
            ],
        );

        ($this->sendEmail)(
            new SwiftEmail(
                'Your account has been removed from Akeneo Supplier Portal',
                $htmlContent,
                $textContent,
                'noreply@akeneo.com',
                $email,
                [$embeddedLogo],
            )
        );
    }
}
