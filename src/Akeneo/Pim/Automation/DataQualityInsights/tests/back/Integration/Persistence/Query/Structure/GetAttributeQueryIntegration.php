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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure\GetAttributeQuery;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;

class GetAttributeQueryIntegration extends TestCase
{
    public function test_that_it_selects_the_attribute_codes()
    {
        $this->createAttribute('attribute_A', false);
        $this->createAttribute('attribute_B', true);
        $this->createAttribute('attribute_without_family', true);

        $this->createFamily('a_family', ['attribute_A', 'attribute_B']);

        $this->assertEquals(
            new Attribute(new AttributeCode('attribute_A'), new AttributeType(AttributeTypes::TEXT), false),
            $this->get(GetAttributeQuery::class)->byAttributeCode(new AttributeCode('attribute_A'))
        );

        $this->assertEquals(
            new Attribute(new AttributeCode('attribute_B'), new AttributeType(AttributeTypes::TEXT), true),
            $this->get(GetAttributeQuery::class)->byAttributeCode(new AttributeCode('attribute_B'))
        );

        $this->assertEquals(
            new Attribute(new AttributeCode('attribute_without_family'), new AttributeType(AttributeTypes::TEXT), true, false),
            $this->get(GetAttributeQuery::class)->byAttributeCode(new AttributeCode('attribute_without_family'))
        );

        $this->assertEquals(
            null,
            $this->get(GetAttributeQuery::class)->byAttributeCode(new AttributeCode('undefined_attribute'))
        );
    }

    private function createAttribute(string $attributeCode, bool $isLocalizable): void
    {
        $attribute = $this
            ->get('akeneo_ee_integration_tests.builder.attribute')
            ->build(['code' => $attributeCode, 'type' => AttributeTypes::TEXT, 'localizable' => $isLocalizable]);

        $this->get('validator')->validate($attribute);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function createFamily(string $familyCode, array $attributeCodes): void
    {
        $familyData = [
            'code' => $familyCode,
            'attributes' => array_merge(['sku'], $attributeCodes)
        ];

        $family = $this
            ->get('akeneo_ee_integration_tests.builder.family')
            ->build($familyData);

        $this->get('validator')->validate($family);
        $this->get('pim_catalog.saver.family')->save($family);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
