<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

use Akeneo\Catalogs\ServiceAPI\Exception\ProductSchemaMappingNotFoundException as ServiceApiProductSchemaMappingNotFoundException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetTargetsQueryInterface
{
    /**
     * @return array<array{code: string, label: string}>|null
     * @throws \JsonException
     * @throws ServiceApiProductSchemaMappingNotFoundException
     */
    public function execute(string $catalogId): ?array;
}
