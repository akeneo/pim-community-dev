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
            $criteriaSubQuery = 'AND pdq.criterion_code IN (:criterion_codes)';
            $queryParameters['criterion_codes'] = $criteria;
            $queryTypes['criterion_codes'] = Connection::PARAM_STR_ARRAY;
        }

        $query = <<<SQL
SELECT DISTINCT BIN_TO_UUID(pcp.uuid) AS product_uuid
FROM pim_catalog_product AS pcp 
 LEFT JOIN pim_data_quality_insights_product_criteria_evaluation AS pdq ON pdq.product_uuid = pcp.uuid $criteriaSubQuery
WHERE pcp.uuid IN (:product_uuids) AND (pdq.evaluated_at IS NULL OR  pdq.evaluated_at < :evaluation_date)
SQL;

        $stmt = $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);

        return ProductUuidCollection::fromStrings($stmt->fetchFirstColumn());
    }
}
