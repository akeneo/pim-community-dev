<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\PerformanceAnalytics\Infrastructure\AntiCorruptionLayer;

use Akeneo\PerformanceAnalytics\Domain\CategoryCode;
use Akeneo\PerformanceAnalytics\Domain\FamilyCode;
use Akeneo\PerformanceAnalytics\Domain\Product\GetProducts;
use Akeneo\PerformanceAnalytics\Domain\Product\Product;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ACLGetProducts implements GetProducts
{
    public function __construct(private GetConnectorProducts $getConnectorProducts)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function byUuids(array $uuids): array
    {
        if ([] === $uuids) {
            return [];
        }

        $connectorProducts = $this->getConnectorProducts->fromProductUuids(
            \array_map(
                fn (string $uuid): UuidInterface => Uuid::fromString($uuid),
                $uuids
            ),
            0,
            null,
            null,
            null
        );

        $products = [];
        /** @var ConnectorProduct $connectorProduct */
        foreach ($connectorProducts->connectorProducts() as $connectorProduct) {
            $products[$connectorProduct->uuid()->toString()] = Product::fromProperties(
                $connectorProduct->uuid(),
                $connectorProduct->createdDate(),
                $connectorProduct->familyCode() ? FamilyCode::fromString($connectorProduct->familyCode()) : null,
                \array_map(
                    fn (string $categoryCode): CategoryCode => CategoryCode::fromString($categoryCode),
                    $connectorProduct->categoryCodes()
                )
            );
        }

        return $products;
    }
}
