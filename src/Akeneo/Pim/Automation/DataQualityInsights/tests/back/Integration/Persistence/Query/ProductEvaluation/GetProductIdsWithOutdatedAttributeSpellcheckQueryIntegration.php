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

namespace Akeneo\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency\EvaluateAttributeSpelling;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\ProductEvaluation\GetProductIdsWithOutdatedAttributeSpellcheckQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

final class GetProductIdsWithOutdatedAttributeSpellcheckQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_retrieves_the_products_with_outdated_attribute_spellchecks()
    {
        $spellcheckEvaluatedSince = new \DateTimeImmutable('2020-06-11 11:09:42');

        $this->givenAttributesWithRecentSpellcheck($spellcheckEvaluatedSince);
        $this->givenAnAttributeWithTooOldSpellcheck($spellcheckEvaluatedSince);
        $this->givenOneProductWithUpToDateAttributeSpellcheckEvaluation($spellcheckEvaluatedSince);
        $this->givenOneProductWithPendingAttributeSpellcheckEvaluation($spellcheckEvaluatedSince);

        $expectedProductsIds = $this->givenProductsWithOutdatedAttributeSpellcheckEvaluation($spellcheckEvaluatedSince);

        $productsIds = $this->get(GetProductIdsWithOutdatedAttributeSpellcheckQuery::class)
            ->evaluatedSince($spellcheckEvaluatedSince, 2);
        $productsIds = iterator_to_array($productsIds);

        $this->assertCount(2, $productsIds);
        $this->assertEqualsCanonicalizing($expectedProductsIds, array_merge(...$productsIds));
    }

    private function givenAttributesWithRecentSpellcheck(\DateTimeImmutable $evaluatedSince): void
    {
        $this->createAttribute('recent_spellcheck');
        $this->createAttributeSpellcheck('recent_spellcheck', $evaluatedSince->modify('+1 second'));
    }

    private function givenAnAttributeWithTooOldSpellcheck(\DateTimeImmutable $evaluatedSince): void
    {
        $this->createAttribute('too_old_spellcheck');
        $this->createFamily('an_old_family', ['too_old_spellcheck']);
        $this->createAttributeSpellcheck('too_old_spellcheck', $evaluatedSince->modify('-1 second'));

        $productId = $this->createProduct('an_old_family');
        $this->createProductEvaluations($productId, $evaluatedSince);
    }

    private function givenProductsWithOutdatedAttributeSpellcheckEvaluation(\DateTimeImmutable $productsEvaluatedAt): array
    {
        $this->createFamily('a_family', ['recent_spellcheck', 'too_old_spellcheck']);
        $this->createFamily('another_family', ['recent_spellcheck']);
        $productsIds = [];

        $productId = $this->createProduct('a_family');
        $productsIds[] = new ProductId($productId);
        $this->createProductEvaluations($productId, $productsEvaluatedAt);

        $productId = $this->createProduct('a_family');
        $productsIds[] = new ProductId($productId);
        $this->createProductEvaluations($productId, $productsEvaluatedAt->modify('-1 minute'));

        $productId = $this->createProduct('another_family');
        $productsIds[] = new ProductId($productId);
        $this->createProductEvaluations($productId, $productsEvaluatedAt);

        // Product without any evaluations
        $productId = $this->createProduct('a_family');
        $this->deleteProductEvaluations($productId);
        $productsIds[] = new ProductId($productId);

        return $productsIds;
    }

    private function givenOneProductWithUpToDateAttributeSpellcheckEvaluation(\DateTimeImmutable $productsEvaluatedAt): void
    {
        $productId = $this->createProduct('a_family');
        $this->createProductEvaluations($productId, $productsEvaluatedAt->modify('+1 minute'));
    }

    private function givenOneProductWithPendingAttributeSpellcheckEvaluation(\DateTimeImmutable $productsEvaluatedAt): void
    {
        $productId = $this->createProduct('a_family');
        $this->createProductEvaluations($productId, $productsEvaluatedAt, true);
    }

    private function createProduct(string $family): int
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier(strval(Uuid::uuid4()))
            ->withFamily($family)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return $product->getId();
    }

    private function createAttribute(string $code): void
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $familyCode, array $attributes): void
    {
        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build([
                'code' => $familyCode,
                'attributes' => $attributes,
            ]);

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createAttributeSpellcheck(string $attributeCode, \DateTimeImmutable $evaluatedAt): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            $evaluatedAt,
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        ));
    }

    private function createProductEvaluations(int $productId, \DateTimeImmutable $evaluatedAt, bool $pending = false): void
    {
        $spellingEvaluation = new Write\CriterionEvaluation(
            new CriterionCode(EvaluateAttributeSpelling::CRITERION_CODE),
            new ProductId($productId),
            CriterionEvaluationStatus::pending()
        );
        $otherEvaluation = new Write\CriterionEvaluation(
            new CriterionCode('spelling'),
            new ProductId($productId),
            CriterionEvaluationStatus::pending()
        );

        $repository = $this->get('akeneo.pim.automation.data_quality_insights.repository.product_criterion_evaluation');
        $evaluations = (new Write\CriterionEvaluationCollection())
            ->add($spellingEvaluation)
            ->add($otherEvaluation);
        $repository->create($evaluations);

        if (false == $pending) {
            $spellingEvaluation->end(new Write\CriterionEvaluationResult());
            $otherEvaluation->end(new Write\CriterionEvaluationResult());
            $repository->update($evaluations);
        }

        $this->updateProductEvaluationsAt($productId, EvaluateAttributeSpelling::CRITERION_CODE, $evaluatedAt);
        $this->updateProductEvaluationsAt($productId, 'spelling', $evaluatedAt->modify('-1 hour'));
    }

    private function updateProductEvaluationsAt(int $productId, string $criterionCode, \DateTimeImmutable $evaluatedAt): void
    {
        $query = <<<SQL
UPDATE pim_data_quality_insights_product_criteria_evaluation
SET evaluated_at = :evaluated_at
WHERE product_id = :product_id AND criterion_code = :criterionCode;
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'evaluated_at' => $evaluatedAt->format(Clock::TIME_FORMAT),
            'product_id' => $productId,
            'criterionCode' => $criterionCode,
        ]);
    }

    private function deleteProductEvaluations(int $productId): void
    {
        $query = <<<SQL
DELETE FROM pim_data_quality_insights_product_criteria_evaluation
WHERE product_id = :productId
SQL;

        $this->get('database_connection')->executeQuery($query, [
            'productId' => $productId,
        ]);
    }
}
