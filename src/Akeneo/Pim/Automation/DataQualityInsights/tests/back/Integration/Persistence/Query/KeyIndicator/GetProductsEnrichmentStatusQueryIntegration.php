<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\KeyIndicator;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfNonRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Enrichment\EvaluateCompletenessOfRequiredAttributes;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\CriterionEvaluationRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\KeyIndicator\GetProductsEnrichmentStatusQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductsEnrichmentStatusQueryIntegration extends DataQualityInsightsTestCase
{
    /** @var CriterionEvaluationRepositoryInterface */
    private $productCriterionEvaluationRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCriterionEvaluationRepository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
    }

    public function test_it_retrieves_products_enrichment_status()
    {
        $this->createChannel('ecommerce', ['locales' => ['en_US', 'fr_FR']]);
        $this->createChannel('mobile', ['locales' => ['en_US']]);

        foreach (['name', 'title', 'description', 'weight'] as $attribute) {
            $this->createAttribute($attribute);
        }

        $this->createFamily('family_with_3_attributes', ['attributes' => ['sku', 'name', 'description']]);
        $this->createFamily('family_with_5_attributes', ['attributes' => ['sku', 'name', 'title', 'description', 'weight']]);

        $expectedProductsEnrichmentStatus = [];
        $expectedProductsEnrichmentStatus += $this->givenProductSampleA();
        $expectedProductsEnrichmentStatus += $this->givenProductSampleB();
        $expectedProductsEnrichmentStatus += $this->givenProductWithoutEvaluations();
        $this->givenNotInvolvedProduct();

        $productIds = array_keys($expectedProductsEnrichmentStatus);
        $productIds[] = 12346; // Unknown product

        $productsEnrichmentStatus = $this->get(GetProductsEnrichmentStatusQuery::class)->execute($productIds);

        $this->assertEquals($expectedProductsEnrichmentStatus, $productsEnrichmentStatus);
    }

    private function givenProductSampleA(): array
    {
        $productId = $this->createProductWithoutEvaluations('sample_A', ['family' => 'family_with_3_attributes'])->getId();

        $expectedEnrichmentStatus = [$productId => [
            'ecommerce' => [
                'en_US' => true,
                'fr_FR' => false,
            ],
            'mobile' => [
                'en_US' => null,
            ]
        ]];

        $requiredCompletenessResult = $this->buildEnrichmentEvaluationResult([
            'ecommerce' => [
                'en_US' => [],
                'fr_FR' => ['name'],
            ]
        ]);
        $nonRequiredCompletenessResult = $this->buildEnrichmentEvaluationResult([
            'ecommerce' => [
                'en_US' => [],
                'fr_FR' => [],
            ]
        ]);

        $this->saveEnrichmentEvaluations(new ProductId($productId), $requiredCompletenessResult, $nonRequiredCompletenessResult);

        return $expectedEnrichmentStatus;
    }

    private function givenProductSampleB(): array
    {
        $productId = $this->createProductWithoutEvaluations('sample_B', ['family' => 'family_with_5_attributes'])->getId();

        $expectedEnrichmentStatus = [$productId => [
            'ecommerce' => [
                'en_US' => true,
                'fr_FR' => false,
            ],
            'mobile' => [
                'en_US' => false,
            ]
        ]];

        $requiredCompletenessResult = $this->buildEnrichmentEvaluationResult([
            'ecommerce' => [
                'en_US' => ['height'],
                'fr_FR' => ['name'],
            ],
        ]);
        $nonRequiredCompletenessResult = $this->buildEnrichmentEvaluationResult([
            'ecommerce' => [
                'en_US' => [],
                'fr_FR' => ['description'],
            ],
            'mobile' => [
                'en_US' => ['brand', 'picture'],
            ]
        ]);

        $this->saveEnrichmentEvaluations(new ProductId($productId), $requiredCompletenessResult, $nonRequiredCompletenessResult);

        return $expectedEnrichmentStatus;
    }

    private function givenNotInvolvedProduct(): void
    {
        $productId = $this->createProductWithoutEvaluations('not_involved_product', ['family' => 'family_with_5_attributes'])->getId();

        $requiredCompletenessResult = $this->buildEnrichmentEvaluationResult([
            'ecommerce' => [
                'en_US' => [],
                'fr_FR' => ['name'],
            ]
        ]);
        $nonRequiredCompletenessResult = $this->buildEnrichmentEvaluationResult([
            'ecommerce' => [
                'en_US' => ['description', 'title'],
                'fr_FR' => [],
            ]
        ]);

        $this->saveEnrichmentEvaluations(new ProductId($productId), $requiredCompletenessResult, $nonRequiredCompletenessResult);
    }

    private function givenProductWithoutEvaluations(): array
    {
        $productWithoutEvaluationsId = $this->createProductWithoutEvaluations(
            'product_without_evaluations',
            ['family' => 'family_with_5_attributes']
        )->getId();

        return [$productWithoutEvaluationsId => [
            'ecommerce' => [
                'en_US' => null,
                'fr_FR' => null,
            ],
            'mobile' => [
                'en_US' => null,
            ]
        ]];
    }

    private function buildEnrichmentEvaluationResult(array $attributesWithoutValue): Write\CriterionEvaluationResult
    {
        $evaluationResult = new Write\CriterionEvaluationResult();

        foreach ($attributesWithoutValue as $channel => $localeAttributes) {
            $channel = new ChannelCode($channel);
            foreach ($localeAttributes as $locale => $attributes) {
                $rateByAttributes = !empty($attributes) ? array_fill_keys($attributes, 0) : [];
                $evaluationResult->addRateByAttributes($channel, new LocaleCode($locale), $rateByAttributes);
            }
        }

        return $evaluationResult;
    }

    private function saveEnrichmentEvaluations(ProductId $productId, Write\CriterionEvaluationResult $requiredCompletenessResult, Write\CriterionEvaluationResult $nonRequiredCompletenessResult): void
    {
        $requiredCompleteness = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateCompletenessOfRequiredAttributes::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::done()
        );
        $nonRequiredCompleteness = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateCompletenessOfNonRequiredAttributes::CRITERION_CODE),
            $productId,
            CriterionEvaluationStatus::done()
        );

        $evaluations = (new Write\CriterionEvaluationCollection())
            ->add($requiredCompleteness)
            ->add($nonRequiredCompleteness);

        $this->productCriterionEvaluationRepository->create($evaluations);

        $requiredCompleteness->end($requiredCompletenessResult);
        $nonRequiredCompleteness->end($nonRequiredCompletenessResult);

        $this->productCriterionEvaluationRepository->update($evaluations);
    }
}
