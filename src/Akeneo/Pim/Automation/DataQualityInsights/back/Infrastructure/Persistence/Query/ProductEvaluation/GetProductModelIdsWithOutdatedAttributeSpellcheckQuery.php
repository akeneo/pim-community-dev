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

final class GetProductModelIdsWithOutdatedAttributeSpellcheckQuery implements GetProductIdsWithOutdatedAttributeSpellcheckQueryInterface
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
SELECT DISTINCT product_model.id
FROM pimee_dqi_attribute_spellcheck AS spellcheck
    INNER JOIN pim_catalog_attribute AS attribute ON attribute.code = spellcheck.attribute_code
    INNER JOIN pim_catalog_family_attribute AS family_attribute ON family_attribute.attribute_id = attribute.id
    INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.family_id = family_attribute.family_id
    INNER JOIN pim_catalog_product_model AS product_model ON product_model.family_variant_id = family_variant.id
    LEFT JOIN pim_data_quality_insights_product_model_criteria_evaluation AS product_model_evaluation
        ON product_model_evaluation.product_id = product_model.id
        AND product_model_evaluation.criterion_code = :criterionCode
WHERE spellcheck.evaluated_at >= :updatedSince
    AND (product_model_evaluation.evaluated_at IS NULL OR spellcheck.evaluated_at > product_model_evaluation.evaluated_at)
    AND (product_model_evaluation.status IS NULL OR product_model_evaluation.status != :pending)
    AND NOT EXISTS(
        SELECT 1
        FROM pim_catalog_variant_attribute_set_has_attributes AS attribute_set_attributes
        INNER JOIN pim_catalog_family_variant_attribute_set AS attribute_set ON attribute_set.id = attribute_set_attributes.variant_attribute_set_id
        INNER JOIN pim_catalog_family_variant_has_variant_attribute_sets AS family_attribute_set ON family_attribute_set.variant_attribute_sets_id = attribute_set.id
        WHERE attribute_set_attributes.attributes_id = attribute.id
          AND family_attribute_set.family_variant_id = family_variant.id
          AND (product_model.parent_id IS NULL OR attribute_set.level = 2)
    );
SQL;

        $stmt = $this->dbConnection->executeQuery($query, [
            'pending' => CriterionEvaluationStatus::PENDING,
            'criterionCode' => EvaluateAttributeSpelling::CRITERION_CODE,
            'updatedSince' => $updatedSince->format(Clock::TIME_FORMAT)
        ]);

        $productModelIds = [];
        while ($productModelId = $stmt->fetchColumn()) {
            $productModelIds[] = $productModelId;

            if (count($productModelIds) >= $bulkSize) {
                yield $this->idFactory->createCollection($productModelIds);
                $productModelIds = [];
            }
        }

        if (!empty($productModelIds)) {
            yield $this->idFactory->createCollection($productModelIds);
        }
    }
}
