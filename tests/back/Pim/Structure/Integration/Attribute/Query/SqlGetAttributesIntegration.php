<?php
declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql\SqlGetAttributes;
use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetAttributesIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->givenAttributes([
            [
                'code' => 'a_boolean',
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
                'labels' => ['en_US' => 'a boolean', 'fr_FR' => 'Un booléen'],
            ],
            [
                'code' => 'a_textarea',
                'type' => AttributeTypes::TEXTAREA,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ],
            [
                'code' => 'a_text',
                'type' => AttributeTypes::TEXT,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ],
            [
                'code' => '123',
                'type' => AttributeTypes::TEXT,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
            ],
            [
                'code' => 'a_locale_specific_attribute',
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => true,
                'scopable' => false,
                'group' => 'other',
                'available_locales' => ['en_US'],
            ],
            [
                'code' => 'a_metric',
                'type' => AttributeTypes::METRIC,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other',
                'metric_family' => 'Length',
                'default_metric_unit' => 'CENTIMETER',
                'decimals_allowed' => true,
                'negative_allowed' => false,
            ]
        ]);
    }

    public function test_it_gets_attributes_by_giving_attribute_codes(): void
    {
        $expected = $this->getExpectedByAttributeCodes();
        $query = $this->getQuery();
        $actual = $query->forCodes(['sku', 'a_text', 'a_boolean', 'a_textarea', 'unknown_attribute_code', '123', 'a_locale_specific_attribute', 'a_metric']);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_gets_attributes_by_giving_attribute_codes_with_cache(): void
    {
        $expected = $this->getExpectedByAttributeCodes();
        $query = $this->getCachedQuery();
        $actual = $query->forCodes(['sku', 'a_text', 'a_boolean', 'a_textarea', 'unknown_attribute_code', '123', 'a_locale_specific_attribute', 'a_metric']);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_gets_attributes_by_given_attribute_type(): void
    {
        $expected = $this->getExpectedByType();
        $actual = $this->getQuery()->forType('pim_catalog_text');
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getExpectedByAttributeCodes(): array
    {
        return [
            'sku' => new Attribute('sku', AttributeTypes::IDENTIFIER, ['reference_data_name' => null], false, false, null, null, false, 'text', [], true, ['en_US' => 'SKU'], true),
            'a_text' => new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []),
            'a_textarea' => new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, null, false, 'textarea', []),
            'a_boolean' => new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, null, false, 'boolean', [], null, ['en_US' => 'a boolean', 'fr_FR' => 'Un booléen']),
            'unknown_attribute_code' => null,
            '123' => new Attribute('123', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []),
            'a_locale_specific_attribute' => new Attribute('a_locale_specific_attribute', AttributeTypes::BOOLEAN, [], true, false, null, null, false, 'boolean', ['en_US']),
            'a_metric' => new Attribute('a_metric', AttributeTypes::METRIC, [], false, false, 'Length', 'CENTIMETER', true, 'metric', []),
        ];
    }

    private function getExpectedByType(): array
    {
        return [
            'a_text' => new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []),
            '123' => new Attribute('123', AttributeTypes::TEXT, [], false, false, null, null, false, 'text', []),
        ];
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getQuery(): SqlGetAttributes
    {
        return $this->get('akeneo.pim.structure.query.sql_get_attributes');
    }

    private function getCachedQuery(): LRUCachedGetAttributes
    {
        return $this->get('akeneo.pim.structure.query.get_attributes');
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
