<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\SqlGetAttributeTranslations;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetAttributeTranslationsIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixture();
    }

    public function test_it_gets_attribute_translations_by_giving_attribute_codes_and_locale_code(): void
    {
        $expected = [
            'a_textarea' => 'une zone de texte',
            'a_boolean' => 'un booléen',
        ];
        $actual = $this->getQuery()->byAttributeCodesAndLocale(['a_boolean', 'a_textarea'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_gets_attribute_translations_by_giving_attribute_codes(): void
    {
        $expected = [
            'a_textarea' => [
                'en_US' => 'a textarea',
                'fr_FR' => 'une zone de texte'
            ],
            'a_boolean' => [
                'en_US' => 'a boolean',
                'fr_FR' => 'un booléen'
            ],
        ];
        $actual = $this->getQuery()->byAttributeCodes(['a_boolean', 'a_textarea']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_nothing_when_searching_on_a_specific_locale_and_attribute_does_not_have_translations(): void
    {
        $expected = [];
        $actual = $this->getQuery()->byAttributeCodesAndLocale(['an_attribute_without_translations'], 'fr_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_nothing_when_attribute_does_not_have_translation_on_a_given_locale(): void
    {
        $expected = [];
        $actual = $this->getQuery()->byAttributeCodesAndLocale(['a_textarea'], 'br_FR');

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_returns_nothing_when_attribute_does_not_have_translations(): void
    {
        $expected = [];
        $actual = $this->getQuery()->byAttributeCodes(['an_attribute_without_translations']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetAttributeTranslations
    {
        return $this->get('akeneo.pim.structure.query.get_attribute_translations');
    }

    private function givenAttributes(array $attributes): void
    {
        $attributes = array_map(function (array $attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);
            $constraintViolations = $this->get('validator')->validate($attribute);

            Assert::count($constraintViolations, 0);

            return $attribute;
        }, $attributes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    private function loadFixture()
    {
        $this->givenAttributes([
            [
                'code' => 'a_boolean',
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
                'labels' => [
                    'en_US' => 'a boolean',
                    'fr_FR' => 'un booléen'
                ]
            ],
            [
                'code' => 'a_textarea',
                'type' => AttributeTypes::TEXTAREA,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
                'labels' => [
                    'en_US' => 'a textarea',
                    'fr_FR' => 'une zone de texte'
                ]
            ],
            [
                'code' => 'an_attribute_without_translations',
                'type' => AttributeTypes::TEXTAREA,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
                'labels' => []
            ]
        ]);
    }
}
