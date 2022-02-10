<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetOptionsCountAndTranslationByAttribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class SqlGetOptionsCountAndTranslationByAttributeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->sqlSearchAttributeOptions = $this->get('akeneo.pim.structure.query.search_attribute_options');

        // Simple Select attributes
        $this->createSimpleAttribute('color', ['en_US' => 'Color', 'fr_FR' => 'Couleur']);
        $this->createAttributeOption('color', 'red', ['fr_FR' => 'Rouge', 'en_US' => 'Red'], 5);
        $this->createAttributeOption('color', 'black', ['fr_FR' => 'Noir', 'en_US' => 'Black'], 4);
        $this->createAttributeOption('color', 'blue', ['fr_FR' => 'Bleu', 'en_US' => 'Blue'], 3);
        $this->createAttributeOption('color', 'brown', ['fr_FR' => 'Brun', 'en_US' => 'Brown'], 2);
        $this->createAttributeOption('color', 'white', ['fr_FR' => 'Blanc', 'en_US' => 'White'], 1);

        $this->createSimpleAttribute('no_trad', []);
        $this->createAttributeOption('no_trad', 'option1', [], 1);

        // Multi Select attributes
        $this->createMultiAttribute('toto', ['fr_FR' => 'TailleToto', 'en_US' => 'totoUs']);
        $this->createAttributeOption('toto', 'optionToto', ['fr_FR' => 'totofr', 'en_US' => 'totous'], 1);

        $this->createMultiAttribute('size', ['en_US' => 'Size', 'fr_FR' => 'Taille']);
        $this->createAttributeOption('size', 'small', ['fr_FR' => 'petit', 'en_US' => 'Small'], 3);
        $this->createAttributeOption('size', 'medium', ['fr_FR' => 'moyen', 'en_US' => 'Medium'], 2);
        $this->createAttributeOption('size', 'large', ['fr_FR' => 'grand', 'en_US' => 'Large'], 1);
    }

    public function test_it_returns_options_count_and_translation_by_attribute_code(): void
    {
        /** @var GetOptionsCountAndTranslationByAttribute $getOptionsCountAndTranslationByAttributeCode */
        $getOptionsCountAndTranslationByAttributeCode =
            $this->get('akeneo.pim.structure.query.get_options_count_and_translation_by_attribute');

        $result = $getOptionsCountAndTranslationByAttributeCode->search('fr_FR');

        $this->assertEqualsCanonicalizing(
            [
                ['code' => 'color', 'label' => 'Couleur', 'options_count' => 5],
                ['code' => 'no_trad', 'label' => null, 'options_count' => 1],
                ['code' => 'size', 'label' => 'Taille', 'options_count' => 3],
                ['code' => 'toto', 'label' => 'TailleToto', 'options_count' => 1],
            ],
            $result,
        );
    }

    public function test_it_returns_options_count_and_translation_by_attribute_code_with_limit_and_offset(): void
    {
        /** @var GetOptionsCountAndTranslationByAttribute $getOptionsCountAndTranslationByAttributeCode */
        $getOptionsCountAndTranslationByAttributeCode =
            $this->get('akeneo.pim.structure.query.get_options_count_and_translation_by_attribute');

        $result = $getOptionsCountAndTranslationByAttributeCode->search(
            'en_US',
            1,
            3
            );

        $this->assertEqualsCanonicalizing(
            [
                [
                    'code' => 'toto',
                    'label' => 'totoUs',
                    'options_count' => 1,
                ],
            ],
            $result,
        );

        $result = $getOptionsCountAndTranslationByAttributeCode->search(
            'en_US',
            2,
            2
        );

        $this->assertEqualsCanonicalizing(
            [
                [
                    'code' => 'size',
                    'label' => 'Size',
                    'options_count' => 3,
                ],
                [
                    'code' => 'toto',
                    'label' => 'totoUs',
                    'options_count' => 1,
                ],
            ],
            $result,
        );
    }

    public function test_it_returns_options_count_and_translation_by_attribute_code_with_label(): void
    {
        /** @var GetOptionsCountAndTranslationByAttribute $getOptionsCountAndTranslationByAttributeCode */
        $getOptionsCountAndTranslationByAttributeCode =
            $this->get('akeneo.pim.structure.query.get_options_count_and_translation_by_attribute');

        $result = $getOptionsCountAndTranslationByAttributeCode->search(
            'fr_FR',
            search:'TAi'
        );

        $this->assertEqualsCanonicalizing(
            [
                [
                    'code' => 'size',
                    'label' => 'Taille',
                    'options_count' => 3,
                ],
                [
                    'code' => 'toto',
                    'label' => 'TailleToto',
                    'options_count' => 1,
                ],
            ],
            $result,
        );
    }

    public function test_it_returns_nothing_when_search_match_with_nothing(): void
    {
        /** @var GetOptionsCountAndTranslationByAttribute $getOptionsCountAndTranslationByAttributeCode */
        $getOptionsCountAndTranslationByAttributeCode =
            $this->get('akeneo.pim.structure.query.get_options_count_and_translation_by_attribute');

        $result = $getOptionsCountAndTranslationByAttributeCode->search(
            'fr_FR',
            search:'unknown attribute'
        );

        $this->assertEqualsCanonicalizing(
            [],
            $result,
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createSimpleAttribute(string $code, array $labels = []): void
    {
        $this->createAttribute($code, $labels);
    }

    private function createMultiAttribute(string $code, array $labels = []): void
    {
        $this->createAttribute($code, $labels, false);
    }

    private function createAttribute(string $code, array $labels, $isSimple = true): void
    {
        $attributes = [];
        $data = [
            'code' => $code,
            'type' => $isSimple ? AttributeTypes::OPTION_SIMPLE_SELECT: AttributeTypes::OPTION_MULTI_SELECT,
            'localizable' => false,
            'scopable' => false,
            'group' => 'other',
            'labels' => $labels,
        ];

        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $violations = $this->get('validator')->validate($attribute);

        Assert::count($violations, 0);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createAttributeOption(string $attributeCode, string $optionCode, array $labels, int $sortOrder): void
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
