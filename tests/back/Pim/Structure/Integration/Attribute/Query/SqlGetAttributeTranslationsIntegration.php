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
    public function test_it_gets_attribute_translations_by_giving_attribute_codes_and_locale_code(): void
    {
        $query = $this->getQuery();

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
            ]
        ]);

        $expected = [
            'a_textarea' => 'une zone de texte',
            'a_boolean' => 'un booléen',
        ];
        $actual = $query->byAttributeCodesAndLocale(['a_boolean', 'a_textarea'], 'fr_FR');

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
}
