<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductUuidsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOutdatedProductUuidsByDateAndCriteriaQuery implements GetOutdatedProductUuidsByDateAndCriteriaQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
    ) {
    }

    /**
     * Retrieves the products that have at least one criterion from a given list that has not been evaluated since a given date
     * If the given criteria list is empty, it will check the date of all criteria
     */
    public function __invoke(ProductUuidCollection $productUuids, \DateTimeImmutable $evaluationDate, array $criteria): ProductUuidCollection
    {
        if ($productUuids->isEmpty()) {
            return $productUuids;
        }

        $queryParameters = [
            'product_uuids' => $productUuids->toArrayBytes(),
            'evaluation_date' => $evaluationDate,
        ];

        $queryTypes = [
            'product_uuids' => Connection::PARAM_STR_ARRAY,
            'evaluation_date' => Types::DATETIME_IMMUTABLE,
        ];

        $criteriaSubQuery = '';
        if (!empty($criteria)) {
            $criteriaSubQuery = 'AND criterion_code IN (:criterion_codes)';
            $queryParameters['criterion_codes'] = $criteria;
            $queryTypes['criterion_codes'] = Connection::PARAM_STR_ARRAY;
        }

        // It's simpler to fetch only products that are up-to-date, because a product may not have criteria evaluation yet in database (just after its creation)
        $query = <<<SQL
SELECT BIN_TO_UUID(product_uuid) AS product_uuid, MIN(COALESCE(evaluated_at >= :evaluation_date, 0)) AS is_up_to_date
FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid IN (:product_uuids) 
$criteriaSubQuery
GROUP BY product_uuid
HAVING is_up_to_date = 1
SQL;

        $stmt = $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);

        $upToDateProductUuids = [];
        while ($row = $stmt->fetchAssociative()) {
            $upToDateProductUuids[$row['product_uuid']] = true;
        }

        $outdatedProductUuids = \array_values(\array_filter(
            $productUuids->toArray(),
            fn (ProductUuid $productUuid) => !\array_key_exists((string) $productUuid, $upToDateProductUuids)
        ));

        return ProductUuidCollection::fromProductUuids($outdatedProductUuids);
    }
}
