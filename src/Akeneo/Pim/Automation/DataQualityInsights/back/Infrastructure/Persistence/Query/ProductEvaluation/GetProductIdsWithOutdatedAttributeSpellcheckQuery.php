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
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Doctrine\DBAL\Connection;

final class GetProductIdsWithOutdatedAttributeSpellcheckQuery implements GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface
{
    public function __construct(
        private Connection                      $dbConnection,
        private ProductEntityIdFactoryInterface $idFactory
    ) {
    }

    /**
     * @return \Generator<int, ProductEntityIdCollection>
     */
    public function evaluatedSince(\DateTimeImmutable $updatedSince, int $bulkSize): \Generator
    {
        $query = <<<SQL
SELECT DISTINCT product.id
FROM pimee_dqi_attribute_spellcheck AS spellcheck
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.code = spellcheck.attribute_code
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_product AS product ON product.family_id = family_attribute.family_id
    LEFT JOIN pim_data_quality_insights_product_criteria_evaluation AS product_evaluation
        ON product_evaluation.product_id = product.id AND product_evaluation.criterion_code = :criterionCode
WHERE spellcheck.evaluated_at >= :updatedSince
    AND (product_evaluation.evaluated_at IS NULL OR spellcheck.evaluated_at > product_evaluation.evaluated_at)
    AND (product_evaluation.status IS NULL OR product_evaluation.status != :pending);
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'pending' => CriterionEvaluationStatus::PENDING,
            'criterionCode' => EvaluateAttributeSpelling::CRITERION_CODE,
            'updatedSince' => $updatedSince->format(Clock::TIME_FORMAT)
        ]);

        $productIds = [];
        while ($productId = $stmt->fetchColumn()) {
            $productIds[] = $productId;

            if (count($productIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productIds);
                $productIds = [];
            }
        }

        if (!empty($productIds)) {
            yield $this->idFactory->createCollection($productIds);
        }
    }
}
