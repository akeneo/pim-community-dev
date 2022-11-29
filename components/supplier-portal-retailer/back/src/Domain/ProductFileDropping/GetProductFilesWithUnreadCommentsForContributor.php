<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface GetProductFilesWithUnreadCommentsForContributor
{
    public function __invoke(string $contributorEmail, \DateTimeImmutable $todayDate): array;
}
