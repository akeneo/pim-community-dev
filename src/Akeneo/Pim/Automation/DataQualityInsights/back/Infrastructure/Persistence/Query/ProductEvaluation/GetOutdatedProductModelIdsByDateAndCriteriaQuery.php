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

        $query = <<<SQL
SELECT DISTINCT pm.id
FROM pim_catalog_product_model AS pm
    LEFT JOIN pim_data_quality_insights_product_model_criteria_evaluation AS pme 
        ON pme.product_id = pm.id $criteriaSubQuery
WHERE pm.id IN (:product_model_ids)
    AND (pme.evaluated_at IS NULL OR  pme.evaluated_at < :evaluation_date)
SQL;

        $stmt = $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);

        return ProductModelIdCollection::fromStrings($stmt->fetchFirstColumn());
    }
}
