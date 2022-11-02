<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping;

interface ListProductFiles
{
    public const NUMBER_OF_PRODUCT_FILES_PER_PAGE = 25;

    public function __invoke(int $page = 1): array;
}
