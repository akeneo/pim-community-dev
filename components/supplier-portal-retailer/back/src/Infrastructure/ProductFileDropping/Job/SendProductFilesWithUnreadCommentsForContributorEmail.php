<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Job;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesWithUnreadCommentsForContributor;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\SendProductFilesWithUnreadCommentsForContributorEmail as SendSummaryEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetAllSuppliersWithContributors;

final class SendProductFilesWithUnreadCommentsForContributorEmail
{
    public function __construct(
        private SendSummaryEmail $sendProductFilesWithUnreadCommentsForContributorEmail,
        private GetProductFilesWithUnreadCommentsForContributor $productFiles,
        private GetAllSuppliersWithContributors $getAllSuppliersWithContributors,
    ) {
    }

    public function sendProductFilesWithUnreadCommentsForContributorEmail(): void
    {
        $suppliersWithContributors = ($this->getAllSuppliersWithContributors)();

        $contributorsEmail = [];

        foreach ($suppliersWithContributors as $supplier) {
            foreach ($supplier->contributors as $contributor) {
                $contributorsEmail[] = $contributor;
            }
        }

        foreach ($contributorsEmail as $contributorEmail) {
            $productFiles = ($this->productFiles)($contributorEmail);
            if (0 < count($productFiles)) {
                ($this->sendProductFilesWithUnreadCommentsForContributorEmail)($productFiles, $contributorEmail);
            }
        }
    }
}
