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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetNumberOfProductsImpactedByAttributeSpellingMistakesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeSpellcheckRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;

class GetNumberOfProductsImpactedByAttributeSpellingMistakesQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_returns_the_number_of_products_impacted_by_a_spelling_mistake_on_an_attribute()
    {
        $this->givenAnAttributeWithASpellingMistake('description');
        $this->givenThreeProductsImpactedByASingleAttributeFromTwoFamilies('description');

        $impactedProducts = $this->get(GetNumberOfProductsImpactedByAttributeSpellingMistakesQuery::class)
            ->byAttributeCode(new AttributeCode('description'));

        $this->assertSame(3, $impactedProducts);
    }

    public function test_it_returns_the_number_of_products_impacted_by_spelling_mistake_on_all_attributes()
    {
        $this->givenAnAttributeWithASpellingMistake('name');
        $this->givenAnAttributeWithASpellingMistake('description');
        $this->givenAnAttributeWithASpellingMistake('weight');
        $this->givenFiveProductsImpactedByThreeAttributes('name', 'description', 'weight');

        $impactedProducts = $this->get(GetNumberOfProductsImpactedByAttributeSpellingMistakesQuery::class)->forAllAttributes();

        $this->assertSame(5, $impactedProducts);
    }

    private function givenAnAttributeWithASpellingMistake(string $attributeCode): void
    {
        $this->createAttribute($attributeCode);
        $this->createAttributeSpellcheckToImprove($attributeCode);
    }

    private function givenThreeProductsImpactedByASingleAttributeFromTwoFamilies(string $attributeToImprove): void
    {
        $this->createAttribute('attribute_without_spelling_mistake');
        $this->createAttributeSpellcheckGood('attribute_without_spelling_mistake');

        $this->createFamily('family_1', ['attribute_without_spelling_mistake', $attributeToImprove]);
        $this->createFamily('family_2', [$attributeToImprove]);
        $this->createFamily('family_3', ['attribute_without_spelling_mistake']);

        $this->createProduct('impacted_product_1', 'family_1');
        $this->createProduct('impacted_product_2', 'family_1');
        $this->createProduct('impacted_product_3', 'family_2');
        $this->createProduct('not_impacted_product', 'family_3');
    }

    private function givenFiveProductsImpactedByThreeAttributes(string $attribute1, string $attribute2, string $attribute3)
    {
        $this->createAttribute('attribute_without_spelling_mistake');
        $this->createAttributeSpellcheckGood('attribute_without_spelling_mistake');

        $this->createFamily('family_1', ['attribute_without_spelling_mistake', $attribute1, $attribute2]);
        $this->createFamily('family_2', [$attribute1, $attribute2, $attribute3]);
        $this->createFamily('family_3', [$attribute3]);
        $this->createFamily('family_without_spelling_mistakes', ['sku', 'attribute_without_spelling_mistake']);

        $this->createProduct('impacted_product_1', 'family_1');
        $this->createProduct('impacted_product_2', 'family_1');
        $this->createProduct('impacted_product_3', 'family_2');
        $this->createProduct('impacted_product_4', 'family_2');
        $this->createProduct('impacted_product_5', 'family_3');
        $this->createProduct('not_impacted_product', 'family_without_spelling_mistakes');
    }

    private function createAttribute(string $code): AttributeInterface
    {
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $code,
            'type' => AttributeTypes::TEXT,
            'group' => 'other',
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
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

    private function createProduct(string $identifier, string $familyCode): void
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->withFamily($familyCode)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);
    }

    private function createAttributeSpellcheckToImprove(string $attributeCode): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::toImprove())
        ));
    }

    private function createAttributeSpellcheckGood(string $attributeCode): void
    {
        $this->get(AttributeSpellcheckRepository::class)->save(new AttributeSpellcheck(
            new AttributeCode($attributeCode),
            new \DateTimeImmutable(),
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
        ));
    }
}
