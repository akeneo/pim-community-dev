<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsImpactedByAttributeGroupActivationQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdsImpactedByAttributeGroupActivationQuery implements GetProductIdsImpactedByAttributeGroupActivationQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    public function updatedSince(\DateTimeImmutable $updatedSince, int $bulkSize): \Iterator
    {
        $impactedFamilies = $this->retrieveFamiliesWithAttributeGroupActivationUpdatedSince($updatedSince);

        if (empty($impactedFamilies)) {
            return;
        }

        $query = <<<SQL
SELECT product.id FROM pim_catalog_product AS product WHERE product.family_id IN (:families)
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $query,
            ['families' => $impactedFamilies],
            ['families' => Connection::PARAM_INT_ARRAY,]
        );

        $productIds = [];
        while ($productId = $stmt->fetchColumn()) {
            $productIds[] = new ProductId(intval($productId));

            if (count($productIds) >= $bulkSize) {
                yield $productIds;
                $productIds = [];
            }
        }

        if (!empty($productIds)) {
            yield $productIds;
        }
    }

    private function retrieveFamiliesWithAttributeGroupActivationUpdatedSince(\DateTimeImmutable $updatedSince): array
    {
        $query = <<<SQL
SELECT DISTINCT family_attribute.family_id
FROM pim_data_quality_insights_attribute_group_activation AS attribute_group_activation
    INNER JOIN pim_catalog_attribute_group AS attribute_group ON attribute_group.code = attribute_group_activation.attribute_group_code
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.group_id = attribute_group.id
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
WHERE attribute_group_activation.updated_at > :updatedSince
SQL;

        $stmt = $this->dbConnection->executeQuery($query, ['updatedSince' => $updatedSince->format(Clock::TIME_FORMAT)]);

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
