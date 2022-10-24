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

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Elasticsearch\Query;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeOptionSpellcheck;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\SpellcheckResultByLocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\SpellCheckResult;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Clock\SystemClock;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Query\GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeOptionSpellcheckRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

final class GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryIntegration extends TestCase
{
    /** @var GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQueryInterface */
    private $query;

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetNumberOfProductsImpactedByAttributeOptionSpellingMistakesQuery::class);
    }

    public function test_it_returns_the_number_of_products_impacted_by_a_spelling_mistake_on_a_not_localizable_attribute_option()
    {
        // Given a spelling mistake on the label of the locale en_US for the the attribute option "optionA"
        $this->createAttributeOptionSpellcheck(
            'a_simple_select',
            'optionA',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
        );

        $this->createAttributeOptionSpellcheck(
            'a_simple_select',
            'optionB',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
        );

        $this->givenALotOfAttributeOptionsToImprove('a_simple_select');

        $productValuesWithSpellingMistake = [
            'a_simple_select' => [[
                'scope' => null,
                'locale' => null,
                'data' => 'optionA'
            ]]
        ];
        $productValuesWithoutSpellingMistake = [
            'a_simple_select' => [[
                'scope' => null,
                'locale' => null,
                'data' => 'optionB'
            ]]
        ];

        // Given two simple products that have the option with spelling mistake in their values
        $this->createProduct($productValuesWithSpellingMistake);
        $this->createProduct($productValuesWithSpellingMistake);
        $this->createProduct($productValuesWithoutSpellingMistake);

        // Given a product model that has the option with spelling mistake in its values
        // To ensure that product models are not counted
        $this->createProductModel($productValuesWithSpellingMistake);

        // Given a product variant whose the product model parent has the option with spelling mistake in its values
        // To ensure that product variants are counted, even if the attribute is on their model
        $parentProductModel = $this->createProductModel([]);
        $subProductModel = $this->createProductModel($productValuesWithSpellingMistake, $parentProductModel->getCode());
        $this->createProductVariant($subProductModel->getCode());

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $impactedProducts = $this->query->byAttribute(new Attribute(new AttributeCode('a_simple_select'), AttributeType::simpleSelect(), false));

        $this->assertSame(3, $impactedProducts);
    }

    public function test_it_returns_the_number_of_products_impacted_by_a_spelling_mistake_on_a_localizable_attribute_option()
    {
        $this->givenALocalizableMultiSelectAttributeWithOptions();

        // Given a spelling mistake on the label of the locale en_US for the the attribute option "optionA"
        $this->createAttributeOptionSpellcheck(
            'a_localizable_multi_select',
            'optionA',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
        );
        $this->createAttributeOptionSpellcheck(
            'a_localizable_multi_select',
            'optionB',
            (new SpellcheckResultByLocaleCollection())
                ->add(new LocaleCode('en_US'), SpellCheckResult::good())
                ->add(new LocaleCode('fr_FR'), SpellCheckResult::good())
        );

        $this->givenALotOfAttributeOptionsToImprove('a_localizable_multi_select');

        // Given two products that have the option with spelling mistake in their values
        $this->createProduct([
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionA', 'optionB']
                ],
                [
                    'scope' => null,
                    'locale' => 'fr_FR',
                    'data' => ['optionA']
                ],
            ],
        ]);
        $this->createProduct([
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionA']
                ],
            ],
            'a_simple_select' => [
                [
                    'scope' => null,
                    'locale' => null,
                    'data' => 'optionA'
                ],
            ],
        ]);
        //Given a product that have the option but in the locale that does not have spelling mistake
        $this->createProduct([
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionC']
                ],
                [
                    'scope' => null,
                    'locale' => 'fr_FR',
                    'data' => ['optionA', 'optionB']
                ],
            ],
        ]);
        //Given a product that does not have the optionA at all in its values
        $this->createProduct([
            'a_localizable_multi_select' => [
                [
                    'scope' => null,
                    'locale' => 'en_US',
                    'data' => ['optionB', 'optionC']
                ],
                [
                    'scope' => null,
                    'locale' => 'fr_FR',
                    'data' => ['optionB']
                ],
            ],
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $impactedProducts = $this->query->byAttribute(new Attribute(new AttributeCode('a_localizable_multi_select'), AttributeType::multiSelect(), true));

        $this->assertSame(2, $impactedProducts);
    }

    private function givenALocalizableMultiSelectAttributeWithOptions(): void
    {
        $attributeCode = 'a_localizable_multi_select';
        $attribute = $this->get('akeneo_integration_tests.base.attribute.builder')->build([
            'code' => $attributeCode,
            'type' => AttributeTypes::OPTION_MULTI_SELECT,
            'group' => 'other',
            'localizable' => true,
        ], true);

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        $this->createAttributeOption($attributeCode, 'optionA');
        $this->createAttributeOption($attributeCode, 'optionB');

        $family = $this->get('pim_catalog.repository.family')->findOneByIdentifier('familyA');
        $this->get('pim_catalog.updater.family')->update($family, [
            'attributes' => array_merge($family->getAttributeCodes(), [$attributeCode])
        ]);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function givenALotOfAttributeOptionsToImprove(string $attributeCode): void
    {
        $optionFactory = $this->get('pim_catalog.factory.attribute_option');
        $optionUpdater = $this->get('pim_catalog.updater.attribute_option');

        // 1024 is the limit of elements allowed in a "query_string" clause in ES
        $options = [];
        for ($i = 1; $i <= 1025; $i++) {
            $optionCode = 'overflow_' . $i;
            $option = $optionFactory->create();
            $optionUpdater->update($option, [
                'code' => $optionCode,
                'attribute' => $attributeCode,
            ]);
            $options[] = $option;
        }

        $this->get('pim_catalog.saver.attribute_option')->saveAll($options);

        foreach ($options as $option) {
            $this->createAttributeOptionSpellcheck(
                $attributeCode,
                $option->getCode(),
                (new SpellcheckResultByLocaleCollection())
                    ->add(new LocaleCode('en_US'), SpellCheckResult::toImprove())
            );
        }
    }

    private function createAttributeOption(string $attributeCode, string $optionCode): AttributeOptionInterface
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => [
                'en_US' => sprintf('%s US', $optionCode),
                'fr_FR' => sprintf('%s FR', $optionCode),
            ],
        ]);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);

        return $attributeOption;
    }

    private function createProduct(array $values): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct(strval(Uuid::uuid4()), 'family_A');

        $this->get('pim_catalog.updater.product')->update($product, ['values' => $values]);
        $this->get('pim_catalog.saver.product')->save($product);

        return $product;
    }

    private function createProductVariant(string $parentCode): void
    {
        $productVariant = $this->get('pim_catalog.builder.product')->createProduct(strval(Uuid::uuid4()), 'family_A');
        $this->get('pim_catalog.updater.product')->update($productVariant, ['parent' => $parentCode]);
        $this->get('pim_catalog.saver.product')->save($productVariant);
    }

    private function createProductModel(array $values, ?string $parentCode = null): ProductModelInterface
    {
        $productModelBuilder = $this->get('akeneo_integration_tests.catalog.product_model.builder')
            ->withCode(strval(Uuid::uuid4()))
            ->withFamilyVariant('familyVariantA1');

        if (null !== $parentCode) {
            $productModelBuilder->withParent($parentCode);
        }

        $productModel = $productModelBuilder->build();

        if (!empty($values)) {
            $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => $values]);
        }

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        return $productModel;
    }

    private function createAttributeOptionSpellcheck(string $attributeCode, string $optionCode, ?SpellcheckResultByLocaleCollection $result = null)
    {
        $attributeOptionSpellcheck = new AttributeOptionSpellcheck(
            new AttributeOptionCode(new AttributeCode($attributeCode), $optionCode),
            $this->get(SystemClock::class)->fromString('2020-05-12 11:23:45'),
            $result ?? new SpellcheckResultByLocaleCollection()
        );

        $this->get(AttributeOptionSpellcheckRepository::class)->save($attributeOptionSpellcheck);
    }
}
