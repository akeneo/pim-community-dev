<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOutdatedProductModelIdsByDateAndCriteriaQuery implements GetOutdatedProductModelIdsByDateAndCriteriaQueryInterface
{
    public function __construct(
        private Connection $dbConnection,
    ) {
    }

    /**
     * Retrieves the product-models that have at least one criterion from a given list that has not been evaluated since a given date
     * If the given criteria list is empty, it will check the date of all criteria
     */
    public function __invoke(ProductModelIdCollection $productModelIds, \DateTimeImmutable $evaluationDate, array $criteria): ProductModelIdCollection
    {
        if ($productModelIds->isEmpty()) {
            return $productModelIds;
        }

        $queryParameters = [
            'product_model_ids' => $productModelIds->toArrayString(),
            'evaluation_date' => $evaluationDate,
        ];

        $queryTypes = [
            'product_model_ids' => Connection::PARAM_STR_ARRAY,
            'evaluation_date' => Types::DATETIME_IMMUTABLE,
        ];

        $criteriaSubQuery = '';
        if (!empty($criteria)) {
            $criteriaSubQuery = 'AND criterion_code IN (:criterion_codes)';
            $queryParameters['criterion_codes'] = $criteria;
            $queryTypes['criterion_codes'] = Connection::PARAM_STR_ARRAY;
        }

        // It's simpler to fetch only product-models that are up-to-date, because they may not have criteria evaluation yet in database (just after their creation)
        $query = <<<SQL
SELECT product_id, MIN(COALESCE(evaluated_at >= :evaluation_date, 0)) AS is_up_to_date
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_model_ids) $criteriaSubQuery
GROUP BY product_id
HAVING is_up_to_date = 1
SQL;

        $stmt = $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);

        $upToDateProductModelIds = [];
        while ($row = $stmt->fetchAssociative()) {
            $upToDateProductModelIds[$row['product_id']] = true;
        }

        $outdatedProductModelIds = \array_values(\array_filter(
            $productModelIds->toArray(),
            fn (ProductModelId $productModelId) => !\array_key_exists((string) $productModelId, $upToDateProductModelIds)
        ));

        return ProductModelIdCollection::fromProductModelIds($outdatedProductModelIds);
    }
}
