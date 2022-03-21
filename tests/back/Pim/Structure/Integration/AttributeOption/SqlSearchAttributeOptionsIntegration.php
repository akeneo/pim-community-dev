<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql\SqlSearchAttributeOptions;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlSearchAttributeOptionsIntegration extends TestCase
{
    private SqlSearchAttributeOptions $sqlSearchAttributeOptions;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlSearchAttributeOptions = $this->get('akeneo.pim.structure.query.search_attribute_options');

        $this->createAttributes(['color']);
        $this->createAttributeOptions('color', 'yellow', [], 6);
        $this->createAttributeOptions('color', 'red', ['fr_FR' => 'Rouge', 'en_US' => 'Red'], 5);
        $this->createAttributeOptions('color', 'black', ['fr_FR' => 'Noir', 'en_US' => 'Black'], 4);
        $this->createAttributeOptions('color', 'blue', ['fr_FR' => 'Bleu', 'en_US' => 'Blue'], 3);
        $this->createAttributeOptions('color', 'brown', ['fr_FR' => 'Brun', 'en_US' => 'Brown'], 2);
        $this->createAttributeOptions('color', 'white', ['fr_FR' => 'Blanc', 'en_US' => 'White'], 1);
    }

    public function test_it_should_return_all_options_without_or_without_locales(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 6,
            'items' => [
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
                [
                    'code' => 'brown',
                    'labels' => ['fr_FR' => 'Brun', 'en_US' => 'Brown'],
                ],
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
                [
                    'code' => 'red',
                    'labels' => ['fr_FR' => 'Rouge', 'en_US' => 'Red'],
                ],
                [
                    'code' => 'yellow',
                    'labels' => [],
                ],
            ]
        ], $searchResult->normalize());
    }

    public function test_it_should_return_all_options_with_locale(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setLocale('en_US');
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 6,
            'items' => [
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
                [
                    'code' => 'brown',
                    'labels' => ['fr_FR' => 'Brun', 'en_US' => 'Brown'],
                ],
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
                [
                    'code' => 'red',
                    'labels' => ['fr_FR' => 'Rouge', 'en_US' => 'Red'],
                ],
                [
                    'code' => 'yellow',
                    'labels' => [],
                ],
            ]
        ], $searchResult->normalize());
    }

    public function test_it_searches_attribute_option_codes_on_all_locales(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 3,
            'items' => [
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_attribute_option_codes_on_a_locale(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setLocale('en_US');
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_orders_attribute_options_on_codes_when_attribute_is_auto_sorted(): void
    {
        $this->updateAttribute('color', ['auto_option_sorting' => true]);

        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 3,
            'items' => [
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_attribute_option_codes_among_an_include_codes_list(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setIncludeCodes(['white', 'black']);
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_attribute_option_codes_among_an_empty_include_codes_list(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setIncludeCodes([]);
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 0,
            'items' => [],
        ], $searchResult->normalize());
    }

    public function test_it_searches_attribute_option_codes_with_an_empty_exclude_codes_list(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setLocale('en_US');
        $searchParameters->setExcludeCodes([]);
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'blue',
                    'labels' => ['fr_FR' => 'Bleu', 'en_US' => 'Blue'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
            ],
        ], $searchResult->normalize());
    }

    public function test_it_searches_attribute_option_codes_and_can_exclude_codes(): void
    {
        $searchParameters = new SearchAttributeOptionsParameters();
        $searchParameters->setSearch('bl');
        $searchParameters->setExcludeCodes(['blue']);
        $searchResult = $this->sqlSearchAttributeOptions->search('color', $searchParameters);

        self::assertEquals([
            'matches_count' => 2,
            'items' => [
                [
                    'code' => 'white',
                    'labels' => ['fr_FR' => 'Blanc', 'en_US' => 'White'],
                ],
                [
                    'code' => 'black',
                    'labels' => ['fr_FR' => 'Noir', 'en_US' => 'Black'],
                ],
            ],
        ], $searchResult->normalize());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributes(array $codes): void
    {
        $attributes = [];
        foreach ($codes as $code) {
            $data = [
                'code' => $code,
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ];

            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
            $violations = $this->get('validator')->validate($attribute);

            Assert::count($violations, 0);
            $attributes[] = $attribute;
        }

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function updateAttribute(string $attributeCode, array $data): void
    {
        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByCode($attributeCode);
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeOptions(string $attributeCode, string $optionCode, array $labels, int $sortOrder): void
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();

        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, [
            'code' => $optionCode,
            'attribute' => $attributeCode,
            'labels' => $labels,
            'sort_order' => $sortOrder,
        ]);
        $constraints = $this->get('validator')->validate($attributeOption);
        Assert::count($constraints, 0);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }
}
