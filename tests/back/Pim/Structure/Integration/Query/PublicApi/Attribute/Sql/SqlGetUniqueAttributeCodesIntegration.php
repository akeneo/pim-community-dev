<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Structure\Integration\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlGetUniqueAttributeCodesIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->getUniqueAttributeCodes = $this->get('pimee_structure.product.query.sql_get_unique_attribute_codes');
    }

    public function test_it_gets_unique_codes_for_a_family()
    {
        $attributes = [
            [
                'code' => 'unique_attribute_1',
                'unique' => true,
            ],
            [
                'code' => 'unique_attribute_2',
                'unique' => true,
            ],
            [
                'code' => 'non_unique_attribute'
            ]
        ];
        $this->givenAttributes($attributes);
        $this->givenFamily(
            [
                'code' => 'familyA',
                'attribute_codes' => ['unique_attribute_1', 'unique_attribute_2', 'non_unique_attribute']
            ]
        );

        $uniqueAttributeCodes = $this->getUniqueAttributeCodes->fromFamilyCode('familyA');

        $this->assertEquals(['sku', 'unique_attribute_1', 'unique_attribute_2'], $uniqueAttributeCodes);
    }

    private function givenFamily(array $familyData)
    {
        $family = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $family,
            [
                'code' => $familyData['code'],
                'attributes'  =>  $familyData['attribute_codes'],
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
