<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetProductFamilyAttributeCodesQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class GetProductFamilyAttributeCodesQueryIntegration extends TestCase
{
    public function test_that_it_selects_the_attribute_codes_of_a_given_family()
    {
        $this->givenADeactivatedAttributeGroup('erp');

        $this->createAttribute('attribute_A');
        $this->createAttribute('attribute_B');
        $this->createAttribute('attribute_C');
        $this->createAttribute('deactivated_attribute', 'erp');

        $this->createFamily('a_family', ['attribute_A', 'attribute_B', 'deactivated_attribute']);

        $productId = $this->createProduct('test', 'a_family');

        $attributeCodes = $this
            ->get(GetProductFamilyAttributeCodesQuery::class)
            ->execute($productId);

        usort($attributeCodes, function (AttributeCode $attributeCode1, AttributeCode $attributeCode2) {
            return strcmp(strval($attributeCode1), strval($attributeCode2));
        });

        $expectedAttributeCodes = [
            new AttributeCode('attribute_A'),
            new AttributeCode('attribute_B'),
            new AttributeCode('sku'),
        ];

        $this->assertEquals($expectedAttributeCodes, $attributeCodes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $attributeCode, string $group = 'other'): void
    {
        $attribute = $this
            ->get('akeneo_ee_integration_tests.builder.attribute')
            ->build([
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'group' => $group,
            ]);

        $this->get('validator')->validate($attribute);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $familyCode, array $attributeCodes): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => array_merge(['sku'], $attributeCodes),
        ];

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build($familyData);

        $this->get('validator')->validate($family);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    private function createProduct(string $identifier, string $familyCode): ProductId
    {
        $product = $this->get('akeneo_integration_tests.catalog.product.builder')
            ->withIdentifier($identifier)
            ->withFamily($familyCode)
            ->build();

        $this->get('pim_catalog.saver.product')->save($product);

        return new ProductId(intval($product->getId()));
    }

    function givenADeactivatedAttributeGroup(string $code): void
    {
        $attributeGroup = $this->get('pim_catalog.factory.attribute_group')->create();
        $this->get('pim_catalog.updater.attribute_group')->update($attributeGroup, ['code' => $code]);
        $this->get('pim_catalog.saver.attribute_group')->save($attributeGroup);

        $attributeGroupActivation = new AttributeGroupActivation(new AttributeGroupCode($code), false);
        $this->get(AttributeGroupActivationRepository::class)->save($attributeGroupActivation);
    }
}
