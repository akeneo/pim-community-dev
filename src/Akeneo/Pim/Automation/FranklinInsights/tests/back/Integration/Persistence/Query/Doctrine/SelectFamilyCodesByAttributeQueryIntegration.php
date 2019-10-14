<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SelectFamilyCodesByAttributeQueryIntegration extends TestCase
{
    public function test_that_it_selects_family_codes_for_a_given_attribute(): void
    {
        $this->createAttribute('test_attribute');
        $this->createFamily('a_test_family', 'test_attribute');

        $familyCodes = $this
            ->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_family_codes_by_attribute_query')
            ->execute('test_attribute')
        ;

        $this->assertSame(['a_test_family'], $familyCodes);
    }

    public function test_that_it_can_return_an_empty_array(): void
    {
        $this->createAttribute('test_attribute');
        $this->createFamily('a_test_family');

        $familyCodes = $this
            ->get('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_family_codes_by_attribute_query')
            ->execute('test_attribute')
        ;

        $this->assertSame([], $familyCodes);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttribute(string $attributeCode): void
    {
        $attribute = $this
            ->get('akeneo_ee_integration_tests.builder.attribute')
            ->build(['code' => $attributeCode, 'type' => AttributeTypes::TEXT]);

        $this->get('validator')->validate($attribute);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $familyCode, ?string $attributeCode = null): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => ['sku'],
        ];

        if (null !== $attributeCode) {
            $familyData['attributes'][] = $attributeCode;
        }

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build($familyData);

        $this->get('validator')->validate($family);

        $this->get('pim_catalog.saver.family')->save($family);
    }
}
