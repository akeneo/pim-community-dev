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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Doctrine\DBAL\Connection;

final class FilterProductIdsWithCriterionNotEvaluatedSinceQuery implements FilterProductIdsWithCriterionNotEvaluatedSinceQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    /** @var string */
    private $tableName;

    public function __construct(Connection $dbConnection, string $tableName)
    {
        $this->dbConnection = $dbConnection;
        $this->tableName = $tableName;
    }

    public function execute(ProductIdCollection $productIds, \DateTimeImmutable $evaluatedSince, CriterionCode $criterionCode): ProductIdCollection
    {
        $tableName = $this->tableName;

        $query = <<<SQL
SELECT product_id
FROM $tableName AS evaluation
WHERE product_id IN (:productIds) AND criterion_code = :criterionCode
    AND status != 'pending' AND (evaluated_at IS NULL OR evaluated_at < :evaluateSince)
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'productIds' => $productIds->toArrayInt(),
            'criterionCode' => $criterionCode,
            'evaluateSince' => $evaluatedSince->format(Clock::TIME_FORMAT),
        ], [
            'productIds' => Connection::PARAM_INT_ARRAY,
        ]);

        return ProductIdCollection::fromStrings($stmt->fetchFirstColumn());
    }
}
