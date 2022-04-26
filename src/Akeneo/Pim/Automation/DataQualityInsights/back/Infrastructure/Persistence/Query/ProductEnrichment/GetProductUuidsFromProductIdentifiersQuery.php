<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductUuidsFromProductIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsFromProductIdentifiersQuery implements GetProductUuidsFromProductIdentifiersQueryInterface
{
    public function __construct(
        private Connection $db,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $productIdentifiers): array
    {
        $query = <<<SQL
SELECT identifier, BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product
WHERE identifier IN (:product_identifiers)
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $productIds = [];
        while ($product = $stmt->fetchAssociative()) {
            $productIds[$product['identifier']] = $this->idFactory->create((string) $product['uuid']);
        }

        return $productIds;
    }
}
