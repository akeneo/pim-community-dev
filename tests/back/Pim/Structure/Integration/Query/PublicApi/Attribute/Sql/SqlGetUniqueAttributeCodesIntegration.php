<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Structure\Integration\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetUniqueAttributeCodesIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->getUniqueAttributeCodes = $this->get('pimee_structure.product.query.sql_get_unique_attribute_codes');
    }

    public function test_it_gets_unique_attribute_codes_from_codes()
    {
        $attributes = [
            [
                'code' => 'unique_attribute',
                'unique' => true,
            ],
            [
                'code' => 'optional_unique_attribute',
                'unique' => true
            ],
            [
                'code' => 'non_unique_attribute'
            ]
        ];
        $this->givenAttributes($attributes);
        $this->givenFamily(
            [
                'code' => 'familyA',
                'attribute_codes' => ['unique_attribute', 'non_unique_attribute']
            ]
        );

        $uniqueAttributeCodes = $this->getUniqueAttributeCodes->all();

        Assert::assertEquals(
            ['sku', 'unique_attribute', 'optional_unique_attribute'],
            $uniqueAttributeCodes
        );
    }

    private function givenFamily(array $familyData)
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code' => $familyData['code'],
                'attributes'  =>  \array_unique(\array_merge(['sku'], $familyData['attribute_codes'])),
                'attribute_requirements' => [],
            ]
        );

        $errors = $this->get('validator')->validate($family);
        Assert::assertEquals(0, $errors->count());

        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function givenAttributes(array $attributesData)
    {
        $attributes = array_map(function ($attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->createAttribute(AttributeTypes::TEXT);
            $this->get('pim_catalog.updater.attribute')->update($attribute, [
                'code' => $attributeData['code'],
                'group' => 'other',
                'unique' => $attributeData['unique'] ?? false,
            ]);
            $errors = $this->get('validator')->validate($attribute);
            Assert::assertEquals(0, $errors->count());

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
