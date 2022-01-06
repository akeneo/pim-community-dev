<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTest\Pim\Structure\Integration\AttributeOption;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetOptionsCountAndTranslationsByAttribute;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class SqlGetOptionsCountAndTranslationsByAttributeCodeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->sqlSearchAttributeOptions = $this->get('akeneo.pim.structure.query.search_attribute_options');

        $this->createSimpleAttribute('color', ['en_US' => 'Color', 'fr_FR' => 'Couleur']);
        $this->createAttributeOption('color', 'red', ['fr_FR' => 'Rouge', 'en_US' => 'Red'], 5);
        $this->createAttributeOption('color', 'black', ['fr_FR' => 'Noir', 'en_US' => 'Black'], 4);
        $this->createAttributeOption('color', 'blue', ['fr_FR' => 'Bleu', 'en_US' => 'Blue'], 3);
        $this->createAttributeOption('color', 'brown', ['fr_FR' => 'Brun', 'en_US' => 'Brown'], 2);
        $this->createAttributeOption('color', 'white', ['fr_FR' => 'Blanc', 'en_US' => 'White'], 1);

        $this->createSimpleAttribute('size', ['en_US' => 'Size', 'fr_FR' => 'Taille']);
        $this->createAttributeOption('size', 'small', ['fr_FR' => 'petit', 'en_US' => 'Small'], 3);
        $this->createAttributeOption('size', 'medium', ['fr_FR' => 'moyen', 'en_US' => 'Medium'], 2);
        $this->createAttributeOption('size', 'large', ['fr_FR' => 'grand', 'en_US' => 'Large'], 1);
    }

    public function test_it_returns_options_count_and_translations_by_attribute_code()
    {
        /** @var GetOptionsCountAndTranslationsByAttribute $getOptionsCountAndTranslationsByAttributeCode */
        $getOptionsCountAndTranslationsByAttributeCode =
            $this->get('akeneo.pim.structure.query.get_options_count_and_translations_by_attribute');
        $result = $getOptionsCountAndTranslationsByAttributeCode->fromAttributesCode(['size']);


        var_dump($result);

        //todo change " to '
        // @todo should return only size
        $this->assertEquals(
            [
                "color" => [
                    "options_count" => 5,
                    "labels" => [
                        'fr_FR' => 'Couleur', 'en_US' => 'Color'
                    ]
                ],
                "size" => [
                    "options_count" => 3,
                    "labels" => [
                        'fr_FR' => 'Taille', 'en_US' => 'Size'
                    ]
                ],
            ],
            json_decode($result, true),
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
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

    private function createSimpleAttribute(string $code, array $labels = []): void
    {
        $this->createAttribute($code, $labels);
    }

    private function createMultiAttribute(string $code, array $labels = []): void
    {
        $this->createAttribute($code, $labels, false);
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
