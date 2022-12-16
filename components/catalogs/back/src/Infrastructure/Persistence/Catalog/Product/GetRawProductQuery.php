<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductQueryInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\GetValuesAndPropertiesFromProductUuids;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 */
class GetRawProductQuery implements GetRawProductQueryInterface
{
    public function __construct(
        private GetValuesAndPropertiesFromProductUuids $getValuesAndPropertiesFromProductUuids,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $productUuid): array|null {

        /** @var array<RawProduct> $rawProducts */
        $rawProducts = $this->getValuesAndPropertiesFromProductUuids->fetchByProductUuids([Uuid::fromString($productUuid)]);

        if ([] === $rawProducts) {
            return null;
        }

        return \array_values($rawProducts)[0];
    }
}
