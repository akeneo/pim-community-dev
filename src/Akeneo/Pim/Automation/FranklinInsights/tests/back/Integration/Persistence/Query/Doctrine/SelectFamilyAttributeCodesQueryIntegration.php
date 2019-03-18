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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class SelectFamilyAttributeCodesQueryIntegration extends TestCase
{
    public function test_that_it_selects_the_attribute_codes_of_a_given_family()
    {
        $this->createAttribute('attribute_A');
        $this->createAttribute('attribute_B');
        $this->createAttribute('attribute_C');

        $this->createFamily('a_family', ['attribute_A', 'attribute_B']);

        $attributeCodes = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_family_attribute_codes')
            ->execute(new FamilyCode('a_family'));

        $expectedAttributeCodes = ['sku', 'attribute_A', 'attribute_B'];

        $this->assertEmpty(array_diff($expectedAttributeCodes, $attributeCodes));
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $attributeCode): void
    {
        $attribute = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')
            ->build(['code' => $attributeCode, 'type' => AttributeTypes::TEXT]);

        $this->getFromTestContainer('validator')->validate($attribute);
        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $familyCode, array $attributeCodes): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => array_merge(['sku'], $attributeCodes),
        ];

        $family = $this
            ->getFromTestContainer('akeneo_ee_integration_tests.builder.family')
            ->build($familyData);

        $this->getFromTestContainer('validator')->validate($family);
        $this->getFromTestContainer('pim_catalog.saver.family')->save($family);
    }
}
