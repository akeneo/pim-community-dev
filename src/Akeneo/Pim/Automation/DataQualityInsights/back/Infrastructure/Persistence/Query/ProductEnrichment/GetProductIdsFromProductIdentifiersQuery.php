<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEnrichment;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetProductIdsFromProductIdentifiersQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
