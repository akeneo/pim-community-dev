<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

final class GetProductIdsFromProductIdentifiersQuery implements GetProductIdsFromProductIdentifiersQueryInterface
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $productIdentifiers): array
    {
        $query = <<<SQL
SELECT identifier, id FROM pim_catalog_product
WHERE identifier IN (:product_identifiers)
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $productIds = [];
        while ($product = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $productIds[$product['identifier']] = new ProductId(intval($product['id']));
        }

        return $productIds;
    }
}
