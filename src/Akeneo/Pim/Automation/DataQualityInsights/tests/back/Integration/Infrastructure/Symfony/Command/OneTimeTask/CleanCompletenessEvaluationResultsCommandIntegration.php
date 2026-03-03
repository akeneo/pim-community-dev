<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Symfony\Command\OneTimeTask;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command\OneTimeTask\OneTimeTaskCommandTrait;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Transformation\TransformCriterionEvaluationResultCodes;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CleanCompletenessEvaluationResultsCommandIntegration extends DataQualityInsightsTestCase
{
    use OneTimeTaskCommandTrait;

    private const COMMAND_NAME = 'pim:data-quality-insights:clean-completeness-evaluation-results';

    public function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->deleteTask(self::COMMAND_NAME);
    }

    public function test_it_cleans_completeness_evaluation_results(): void
    {
        $cleanProductUuid = $this->givenAProductWithCleanCompletenessResults();
        $aDirtyProductUuid = $this->givenAProductWithDirtyCompletenessResults();
        $anotherDirtyProductUuid = $this->givenAnotherProductWithDirtyCompletenessResults();
        $aDirtyProductModelUuid = $this->givenAProductModelWithDirtyCompletenessResults();

        $this->launchCleaning();

        $this->assertDirtyProductHasBeenCleaned($aDirtyProductUuid);
        $this->assertAnotherDirtyProductHasBeenCleaned($anotherDirtyProductUuid);
        $this->assertCleanProductHasNoChanges($cleanProductUuid);
        $this->assertDirtyProductModelHasBeenCleaned($aDirtyProductModelUuid);
    }

    private function launchCleaning(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND_NAME);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()], ['capture_stderr_separately' => true]);

        self::assertEquals(0, $commandTester->getStatusCode(), $commandTester->getErrorOutput());
    }

    private function givenAProductWithCleanCompletenessResults(): UuidInterface
    {
        $productUuid = $this->createProduct('a_clean_product')->getUuid();

        $this->updateProductCriterionResult(
            $productUuid,
            EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
            $this->buildCleanResult(5)
        );
        $this->updateProductCriterionResult(
            $productUuid,
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
            $this->buildCleanResult(1)
        );

        return $productUuid;
    }

    private function givenAProductWithDirtyCompletenessResults(): UuidInterface
    {
        $productUuid = $this->createProduct('a_dirty_product')->getUuid();

        $this->updateProductCriterionResult(
            $productUuid,
            EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
            $this->buildDirtyResult([12, 45, 64])
        );
        $this->updateProductCriterionResult(
            $productUuid,
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
            $this->buildDirtyResult([99, 57])
        );

        return $productUuid;
    }

    private function givenAnotherProductWithDirtyCompletenessResults(): UuidInterface
    {
        $productUuid = $this->createProduct('another_dirty_product')->getUuid();

        $this->updateProductCriterionResult(
            $productUuid,
            EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
            $this->buildDirtyResult([89])
        );
        $this->updateProductCriterionResult(
            $productUuid,
            EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE,
            $this->buildDirtyResult([])
        );

        return $productUuid;
    }

    private function assertDirtyProductHasBeenCleaned(UuidInterface $productUuid): void
    {
        $result = $this->getProductCriterionResult($productUuid, EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $this->assertEqualsCanonicalizing($result, $this->buildCleanResult(3), 'The completeness of non required attributes should have been cleaned for the dirty product');

        $result = $this->getProductCriterionResult($productUuid, EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);
        $this->assertEqualsCanonicalizing($result, $this->buildCleanResult(2), 'The completeness of required attributes should have been cleaned for the dirty product');
    }

    private function assertAnotherDirtyProductHasBeenCleaned(UuidInterface $productUuid): void
    {
        $result = $this->getProductCriterionResult($productUuid, EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $this->assertEqualsCanonicalizing($result, $this->buildCleanResult(1), 'The completeness of non required attributes should have been cleaned for the another dirty product');

        $result = $this->getProductCriterionResult($productUuid, EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);
        $this->assertEqualsCanonicalizing($result, $this->buildCleanResult(0), 'The completeness of required attributes should have been cleaned for the another dirty product');
    }

    private function assertCleanProductHasNoChanges(UuidInterface $productUuid): void
    {
        $result = $this->getProductCriterionResult($productUuid, EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE);
        $this->assertEqualsCanonicalizing($result, $this->buildCleanResult(5), 'The completeness of non required attributes should not have been changed for the clean product');

        $result = $this->getProductCriterionResult($productUuid, EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE);
        $this->assertEqualsCanonicalizing($result, $this->buildCleanResult(1), 'The completeness of required attributes should not have been changed for the clean product');
    }

    private function buildDirtyResult(array $attributeList): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['attributes_with_rates'] => [
                    1 => [39 => $attributeList, 58 => $attributeList],
                    2 => [39 => $attributeList],
                ],
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                    1 => [39 => 42, 58 => 42],
                    2 => [39 => 42],
                ],
            ]
        ];
    }

    private function buildCleanResult(int $numberOfAttributes): array
    {
        return [
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['rates'] => [],
            TransformCriterionEvaluationResultCodes::PROPERTIES_ID['data'] => [
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['total_number_of_attributes'] => [
                    1 => [39 => 42, 58 => 42],
                    2 => [39 => 42],
                ],
                TransformCriterionEvaluationResultCodes::DATA_TYPES_ID['number_of_improvable_attributes'] => [
                    1 => [39 => $numberOfAttributes, 58 => $numberOfAttributes],
                    2 => [39 => $numberOfAttributes],
                ],
            ]
        ];
    }

    private function updateProductCriterionResult(UuidInterface $productUuid, string $criterionCode, array $result): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation
SET result = :result 
WHERE product_uuid = :productUuid AND criterion_code = :criterionCode;
SQL;

        $this->dbConnection->executeQuery($query, [
            'productUuid' => $productUuid->getBytes(),
            'criterionCode' => $criterionCode,
            'result' => \json_encode($result)
        ], [
            'productUuid' => \PDO::PARAM_STR,
        ]);
    }

    private function getProductCriterionResult(UuidInterface $productUuid, string $criterionCode): ?array
    {
        $query = <<<SQL
SELECT result FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_uuid = :productUuid AND criterion_code = :criterionCode;
SQL;

        $result = $this->dbConnection->executeQuery($query, [
            'productUuid' => $productUuid->getBytes(),
            'criterionCode' => $criterionCode
        ], [
            'productUuid' => \PDO::PARAM_STR,
        ])->fetchOne();

        return $result ? \json_decode($result, true) : null;
    }

    private function givenAProductModelWithDirtyCompletenessResults(): int
    {
        $this->createMinimalFamilyAndFamilyVariant('a_family', 'a_family_variant');
        $productModelId = $this->createProductModel('a_dirty_product_model', 'a_family_variant')->getId();
        $dirtyResult = $this->buildDirtyResult([34, 6, 76]);

        $query = <<<SQL
UPDATE pim_data_quality_insights_product_model_criteria_evaluation
SET result = :result 
WHERE product_id = :productModelId AND criterion_code = :criterionCode;
SQL;

        $this->dbConnection->executeQuery($query, [
            'productModelId' => $productModelId,
            'criterionCode' => EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE,
            'result' => \json_encode($dirtyResult)
        ]);

        return $productModelId;
    }

    private function assertDirtyProductModelHasBeenCleaned(int $productModelId): void
    {
        $query = <<<SQL
SELECT result FROM pim_data_quality_insights_product_model_criteria_evaluation
WHERE product_id = :productModelId AND criterion_code = :criterionCode;
SQL;

        $result = $this->dbConnection->executeQuery($query, [
            'productModelId' => $productModelId,
            'criterionCode' => EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE
        ])->fetchOne();

        $this->assertEquals(\json_decode($result, true), $this->buildCleanResult(3), 'The completeness results should have been cleaned for the dirty product model');
    }
}
