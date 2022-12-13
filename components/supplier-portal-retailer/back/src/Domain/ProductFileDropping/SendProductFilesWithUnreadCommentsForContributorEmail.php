<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface SendProductFilesWithUnreadCommentsForContributorEmail
{
    public function __invoke(array $productFileWithUnreadComments, string $contributorEmail): void;
}
