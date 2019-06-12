<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        ]);
    }

    public function test_it_gets_attributes_by_giving_attribute_codes(): void
    {
        $expected = $this->getExpected();
        $query = $this->getQuery();
        $actual = $query->forCodes(['a_text', 'a_boolean', 'a_textarea']);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function test_it_gets_attributes_by_giving_attribute_codes_with_cache(): void
    {
        $expected = $this->getExpected();
        $query = $this->getCachedQuery();
        $actual = $query->forCodes(['a_text', 'a_boolean', 'a_textarea']);
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function getExpected(): array
    {
        return [
            'a_text' => new Attribute('a_text', AttributeTypes::TEXT, [], false, false, null, false),
            'a_textarea' => new Attribute('a_textarea', AttributeTypes::TEXTAREA, [], false, false, null, false),
            'a_boolean' => new Attribute('a_boolean', AttributeTypes::BOOLEAN, [], false, false, null, false),
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
            $constraints = $this->get('validator')->validate($attribute);

            Assert::count($constraints, 0);

            return $attribute;
        }, $attributes);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }
}
