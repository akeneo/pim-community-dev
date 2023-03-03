<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Catalog;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCategoryCodesByProductQueryInterface
{
    /**
     * @param string[] $productUuids
     * @return array<string, string[]> $locales
     */
    public function execute(array $productUuids): array;
}
