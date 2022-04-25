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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Doctrine\DBAL\Connection;

final class FilterProductIdsWithCriterionNotEvaluatedSinceQuery implements FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface
{
    public function __construct(
        private Connection                      $dbConnection,
        private string                          $tableName,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    public function execute(ProductEntityIdCollection $productIds, \DateTimeImmutable $evaluatedSince, CriterionCode $criterionCode): ProductEntityIdCollection
    {
        $tableName = $this->tableName;

        $query = <<<SQL
SELECT product_id
FROM $tableName AS evaluation
WHERE product_id IN (:productIds) AND criterion_code = :criterionCode
    AND status != 'pending' AND (evaluated_at IS NULL OR evaluated_at < :evaluateSince)
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'productIds' => array_map(fn ($productId) => (int)$productId, $productIds->toArrayString()),
            'criterionCode' => $criterionCode,
            'evaluateSince' => $evaluatedSince->format(Clock::TIME_FORMAT),
        ], [
            'productIds' => Connection::PARAM_INT_ARRAY,
        ]);


        return $this->idFactory->createCollection($stmt->fetchFirstColumn());
    }
}
