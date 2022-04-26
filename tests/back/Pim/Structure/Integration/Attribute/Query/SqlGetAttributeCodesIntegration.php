<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Bundle\Query\InternalApi\Attribute\SqlGetAttributeCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeCodes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetAttributeCodesIntegration extends TestCase
{
    private SqlGetAttributeCodes $getAttributeCodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->getAttributeCodes = $this->get(GetAttributeCodes::class);

        $this->givenAttributes([
            [
                'code' => 'a_boolean',
                'type' => AttributeTypes::BOOLEAN,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
            ],
            [
                'code' => 'a_textarea',
                'type' => AttributeTypes::TEXTAREA,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
            ],
            [
                'code' => 'a_text',
                'type' => AttributeTypes::TEXT,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
            ],
            [
                'code' => '123',
                'type' => AttributeTypes::TEXT,
                'localizable' => false,
                'scopable' => false,
                'group' => 'other'
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

    public function test_it_returns_attribute_codes(): void
    {
        self::assertEquals([], $this->getAttributeCodes->forAttributeTypes([]));
        self::assertEqualsCanonicalizing(
            ['a_text', '123'],
            $this->getAttributeCodes->forAttributeTypes([AttributeTypes::TEXT])
        );
        self::assertEqualsCanonicalizing(
            ['a_text', '123', 'a_metric'],
            $this->getAttributeCodes->forAttributeTypes([AttributeTypes::METRIC, AttributeTypes::TEXT])
        );
        self::assertEqualsCanonicalizing(
            [],
            $this->getAttributeCodes->forAttributeTypes(['unknown'])
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function givenAttributes(array $attributes): void
    {
        $attributes = array_map(function (array $attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);
            $constraintViolationss = $this->get('validator')->validate($attribute);

            Assert::count($constraintViolationss, 0);

            return $attribute;
        }, $attributes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }
}
