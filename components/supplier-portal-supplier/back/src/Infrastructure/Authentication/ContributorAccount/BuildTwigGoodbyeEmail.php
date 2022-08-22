<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\BuildGoodbyeEmail;
use Akeneo\SupplierPortal\Supplier\Domain\Mailer\Email;
use Twig\Environment;

class BuildTwigGoodbyeEmail implements BuildGoodbyeEmail
{
    public function __construct(private Environment $twig, private string $assetsPath)
    {
    }

    public function __invoke(string $email): Email
    {
        $embededLogo = \Swift_Image::fromPath(sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png'));

        $htmlContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.html.twig',
            [
                'contributorEmail' => $email,
                'logoCID' => $embededLogo->getId(),
            ],
        );

        $textContent = $this->twig->render(
            '@AkeneoSupplierPortalSupplier/Email/contributor-removed.txt.twig',
            [
                'contributorEmail' => $email,
            ],
        );

        return new Email(
            'Your account has been removed from Akeneo Supplier Portal',
            $htmlContent,
            $textContent,
            'noreply@akeneo.com',
            $email,
            [$embededLogo],
        );
    }
}
