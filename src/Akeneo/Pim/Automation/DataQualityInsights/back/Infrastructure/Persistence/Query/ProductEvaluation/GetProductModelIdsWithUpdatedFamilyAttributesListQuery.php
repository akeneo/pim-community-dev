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
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\UpdatedFamily;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetProductIdsWithUpdatedFamilyAttributesListQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamiliesWithUpdatedAttributesListQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Doctrine\DBAL\Connection;

final class GetProductModelIdsWithUpdatedFamilyAttributesListQuery implements GetProductIdsWithUpdatedFamilyAttributesListQueryInterface
{
    public function __construct(
        private Connection                                         $dbConnection,
        private GetFamiliesWithUpdatedAttributesListQueryInterface $getFamiliesWithUpdatedAttributesListQuery,
        private ProductEntityIdFactoryInterface                    $idFactory
    ) {
    }

    /**
     * @return \Generator<int, ProductEntityIdCollection>
     */
    public function updatedSince(\DateTimeImmutable $updatedSince, int $bulkSize): \Generator
    {
        $updatedFamilies = $this->getFamiliesWithUpdatedAttributesListQuery->updatedSince($updatedSince);

        $query = <<<SQL
SELECT product_model.id
FROM pim_catalog_product_model AS product_model
INNER JOIN pim_catalog_family_variant AS family_variant ON family_variant.id = product_model.family_variant_id
LEFT JOIN pim_data_quality_insights_product_model_criteria_evaluation AS product_model_evaluation
    ON product_model_evaluation.product_id = product_model.id
    AND product_model_evaluation.criterion_code = :criterionCode
    AND (product_model_evaluation.evaluated_at >= :updatedSince OR product_model_evaluation.status = 'pending')
WHERE family_variant.family_id = :familyId
    AND product_model_evaluation.product_id IS NULL;
SQL;
        $stmt = $this->dbConnection->prepare($query);
        $productModelIds = [];

        /** @var UpdatedFamily $updatedFamily */
        foreach ($updatedFamilies as $updatedFamily) {
            $stmt->execute([
                'criterionCode' => EvaluateAttributeSpelling::CRITERION_CODE,
                'familyId' => $updatedFamily->getFamilyId(),
                'updatedSince' => $updatedFamily->updatedAt()->format(Clock::TIME_FORMAT),
            ]);

            while ($productModelId = $stmt->fetchColumn()) {
                $productModelIds[] = $productModelId;

                if (count($productModelIds) >= $bulkSize) {
                    yield $this->idFactory->createCollection($productModelIds);
                    $productModelIds = [];
                }
            }
        }

        if (!empty($productModelIds)) {
            yield $this->idFactory->createCollection($productModelIds);
        }
    }
}
