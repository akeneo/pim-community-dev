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

        /**
         * Because a product may not have criteria evaluation yet in database (just after its creation)
         * We determine in the query if the product is outdated or up-to-date
         * So the products that are not in the results can be considered as outdated
         */
        $query = <<<SQL
WITH product_evaluations AS (
    SELECT product_uuid, MAX(COALESCE(evaluated_at < :evaluation_date, 1)) AS is_outdated
    FROM pim_data_quality_insights_product_criteria_evaluation
    WHERE product_uuid IN (:product_uuids) 
    $criteriaSubQuery
    GROUP BY product_uuid
) 
SELECT BIN_TO_UUID(product_uuid) AS product_uuid, is_outdated 
FROM product_evaluations;
SQL;

        $stmt = $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);

        $areProductUuidsOutdated = [];
        while ($row = $stmt->fetchAssociative()) {
            $areProductUuidsOutdated[$row['product_uuid']] = \boolval($row['is_outdated']);
        }
        
        $outdatedProductUuids = \array_values(\array_filter(
            $productUuids->toArray(),
            function (ProductUuid $productUuid) use ($areProductUuidsOutdated) {
                $productUuidString = (string) $productUuid;
                return !\array_key_exists($productUuidString, $areProductUuidsOutdated)
                    || true === $areProductUuidsOutdated[$productUuidString];
            }
        ));

        return ProductUuidCollection::fromProductUuids($outdatedProductUuids);
    }
}
