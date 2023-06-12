<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\tests\back\Integration\Infrastructure\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateImageEnrichment;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEvaluation\GetOutdatedProductUuidsByDateAndCriteriaQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuid;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetOutdatedProductUuidsByDateAndCriteriaQueryIntegration extends DataQualityInsightsTestCase
{
    private Connection $dbConnection;
    private Clock $clock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->clock = $this->get(SystemClock::class);

        $this->createAttribute('name');
        $this->createFamily('shoes', ['attributes' => ['sku', 'name']]);
        $this->deleteAllProductCriterionEvaluations();
    }

    public function test_it_returns_outdated_product_uuids_for_some_criteria(): void
    {
        $outdatedProduct1 = $this->createProduct('outdated_product_1', ['family' => 'shoes']);
        $outdatedProduct2 = $this->createProduct('outdated_product_2', ['family' => 'shoes']);
        $productWithoutEvaluation = $this->createProduct('product_without_evaluation', ['family' => 'shoes']);
        $productWithoutEvaluationDate = $this->createProduct('product_without_evaluation_date', ['family' => 'shoes']);
        $upToDatedProduct1 = $this->createProduct('up_to_date_product_1', ['family' => 'shoes']);
        $upToDatedProduct2 = $this->createProduct('up_to_date_product_2', ['family' => 'shoes']);
        $whateverProduct = $this->createProduct('whatever_product', ['family' => 'shoes']);

        $evaluationDate = $this->clock->fromString('2023-03-15 16:21:35');
        $criteria = [
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
            EvaluateImageEnrichment::CRITERION_CODE,
        ];

        $outdatedProductUuid1 = ProductUuid::fromUuid($outdatedProduct1->getUuid());
        $this->updateProductCriteriaEvaluationsAt($outdatedProductUuid1, $evaluationDate->modify('-1 second'), $criteria);

        $outdatedProductUuid2 = ProductUuid::fromUuid($outdatedProduct2->getUuid());
        $this->updateProductCriteriaEvaluationsAt($outdatedProductUuid2, $evaluationDate->modify('+1 second'), []);
        $this->updateProductCriteriaEvaluationsAt($outdatedProductUuid2, $evaluationDate->modify('-1 second'), [EvaluateImageEnrichment::CRITERION_CODE]);

        $upToDatedProductUuid1 = ProductUuid::fromUuid($upToDatedProduct1->getUuid());
        $this->updateProductCriteriaEvaluationsAt($upToDatedProductUuid1, $evaluationDate, []);

        $upToDatedProductUuid2 = ProductUuid::fromUuid($upToDatedProduct2->getUuid());
        $this->updateProductCriteriaEvaluationsAt($upToDatedProductUuid2, $evaluationDate->modify('-1 hour'), []);
        $this->updateProductCriteriaEvaluationsAt($upToDatedProductUuid2, $evaluationDate->modify('+1 hour'), $criteria);

        $productWithoutEvaluationDateUuid = ProductUuid::fromUuid($productWithoutEvaluationDate->getUuid());
        $productWithoutEvaluationUuid = ProductUuid::fromUuid($productWithoutEvaluation->getUuid());
        $this->deleteProductCriterionEvaluations($productWithoutEvaluation->getUuid());

        $this->updateProductCriteriaEvaluationsAt(ProductUuid::fromUuid($whateverProduct->getUuid()), $evaluationDate->modify('-1 hour'), []);

        $outdatedProductUuids = ($this->get(GetOutdatedProductUuidsByDateAndCriteriaQueryInterface::class))(ProductUuidCollection::fromProductUuids([
            $outdatedProductUuid1,
            $outdatedProductUuid2,
            $productWithoutEvaluationUuid,
            $productWithoutEvaluationDateUuid,
            $upToDatedProductUuid1,
            $upToDatedProductUuid2,
        ]), $evaluationDate, $criteria);

        Assert::assertEqualsCanonicalizing([
            $outdatedProductUuid1->__toString(),
            $outdatedProductUuid2->__toString(),
            $productWithoutEvaluationUuid->__toString(),
            $productWithoutEvaluationDateUuid->__toString(),
        ], $outdatedProductUuids->toArrayString());
    }


    public function test_it_returns_outdated_product_uuids_for_any_criterion(): void
    {
        $outdatedProduct1 = $this->createProduct('outdated_product_1', ['family' => 'shoes']);
        $outdatedProduct2 = $this->createProduct('outdated_product_2', ['family' => 'shoes']);
        $upToDatedProduct = $this->createProduct('up_to_date_product', ['family' => 'shoes']);

        $evaluationDate = $this->clock->fromString('2023-03-15 16:21:35');

        $outdatedProductUuid1 = ProductUuid::fromUuid($outdatedProduct1->getUuid());
        $this->updateProductCriteriaEvaluationsAt($outdatedProductUuid1, $evaluationDate->modify('-1 second'), []);

        $outdatedProductUuid2 = ProductUuid::fromUuid($outdatedProduct2->getUuid());
        $this->updateProductCriteriaEvaluationsAt($outdatedProductUuid2, $evaluationDate->modify('+1 second'), []);
        $this->updateProductCriteriaEvaluationsAt($outdatedProductUuid2, $evaluationDate->modify('-1 second'), [EvaluateImageEnrichment::CRITERION_CODE]);

        $upToDatedProductUuid = ProductUuid::fromUuid($upToDatedProduct->getUuid());
        $this->updateProductCriteriaEvaluationsAt($upToDatedProductUuid, $evaluationDate, []);

        $outdatedProductUuids = ($this->get(GetOutdatedProductUuidsByDateAndCriteriaQueryInterface::class))(ProductUuidCollection::fromProductUuids([
            $outdatedProductUuid1,
            $outdatedProductUuid2,
            $upToDatedProductUuid,
        ]), $evaluationDate, []);

        Assert::assertEquals([$outdatedProductUuid1->__toString(), $outdatedProductUuid2->__toString()], $outdatedProductUuids->toArrayString());
    }

    private function updateProductCriteriaEvaluationsAt(ProductUuid $productUuid, \DateTimeImmutable $evaluatedAt, array $criteria): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation
SET evaluated_at = :evaluated_at 
WHERE product_uuid = :product_uuid
SQL;
        $queryParameters = [
            'product_uuid' => $productUuid->toBytes(),
            'evaluated_at' => $evaluatedAt,
        ];

        $queryTypes = [
            'product_uuid' => Types::BINARY,
            'evaluated_at' => Types::DATETIME_IMMUTABLE,
        ];

        if (!empty($criteria)) {
            $query .= ' AND criterion_code IN (:criteria)';
            $queryParameters['criteria'] = $criteria;
            $queryTypes['criteria'] = Connection::PARAM_STR_ARRAY;
        }

        $this->dbConnection->executeQuery($query, $queryParameters, $queryTypes);
    }
}
