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

        /**
         * Because a product-model may not have criteria evaluation yet in database (just after its creation)
         * We determine in the query if the product-model is outdated or up-to-date
         * So the products that are not in the results can be considered as outdated
         */
        $query = <<<SQL
SELECT product_id, MAX(COALESCE(evaluated_at < :evaluation_date, 1)) AS is_outdated
FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id IN (:product_model_ids) $criteriaSubQuery
GROUP BY product_id
SQL;

        $stmt = $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);

        $areProductModelIdsOutdated = [];
        while ($row = $stmt->fetchAssociative()) {
            $areProductModelIdsOutdated[$row['product_id']] = \boolval($row['is_outdated']);
        }

        $outdatedProductModelIds = \array_values(\array_filter(
            $productModelIds->toArray(),
            function (ProductModelId $productModelId) use ($areProductModelIdsOutdated) {
                $productModelIdString = (string) $productModelId;
                return !\array_key_exists($productModelIdString, $areProductModelIdsOutdated)
                    || true === $areProductModelIdsOutdated[$productModelIdString];
            }
        ));

        return ProductModelIdCollection::fromProductModelIds($outdatedProductModelIds);
    }
}
