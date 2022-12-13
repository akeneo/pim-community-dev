<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\Email;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\SendProductFilesWithUnreadCommentsForContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\SendEmail;

final class SendTwigProductFilesWithUnreadCommentsForContributorEmail implements SendProductFilesWithUnreadCommentsForContributorEmail
{
    public function __construct(
        private SendEmail $sendEmail,
        private string $assetsPath,
    ) {
    }

    public function __invoke(array $productFileWithUnreadComments, string $contributorEmail): void
    {
        $embeddedLogoPath = sprintf('%s/%s', $this->assetsPath, 'images/supplier_portal_logo.png');

        $contributorEmail = new Email(
            'You have unread comments from your contact on the Supplier Portal',
            '@AkeneoSupplierPortalRetailer/Email/product-files-with-unread-comments-for-contributor.html.twig',
            '@AkeneoSupplierPortalRetailer/Email/product-files-with-unread-comments-for-contributor.txt.twig',
            [
                'contributorEmail' => $contributorEmail,
                'productFiles' => $productFileWithUnreadComments,
            ],
            $contributorEmail,
            $embeddedLogoPath,
        );
        ($this->sendEmail)($contributorEmail);
    }
}
