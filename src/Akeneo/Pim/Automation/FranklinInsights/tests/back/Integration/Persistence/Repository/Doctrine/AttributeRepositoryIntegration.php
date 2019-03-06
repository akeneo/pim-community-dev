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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Integration\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class AttributeRepositoryIntegration extends TestCase
{
    public function test_find_one_by_identifier(): void
    {
        $attribute = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('color');
        $this->assertNull($attribute);

        $id = $this->createAttributeWithAllValues('color');

        $attribute = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('color');

        $expectedAttribute = new Attribute(
            new AttributeCode('color'),
            $id,
            'pim_catalog_identifier',
            true,
            true,
            true,
            true,
            [
                'en_US' => 'An attribute',
                'fr_FR' => 'Un attribut',
            ],
            'Length',
            'METER'
        );
        $this->assertEquals($expectedAttribute, $attribute);

        $id2 = $this->createAttributeWithMinimumValues('size');
        $attribute = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('size');

        $expectedAttribute = new Attribute(
            new AttributeCode('size'),
            $id2,
            'pim_catalog_text',
            false,
            false,
            false,
            false,
            [],
            null,
            null
        );
        $this->assertEquals($expectedAttribute, $attribute);
    }

    public function test_find_by_codes(): void
    {
        $attributes = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('color');
        $this->assertEmpty($attributes);

        $this->createAttributeWithAllValues('color');
        $this->createAttributeWithMinimumValues('size');

        $attributes = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findByCodes(['color', 'size']);
        $this->assertCount(2, $attributes);
        $this->assertContainsOnlyInstancesOf(Attribute::class, $attributes);
    }

    public function test_get_attribute_type_by_codes(): void
    {
        $this->createAttributeWithAllValues('color');
        $this->createAttributeWithMinimumValues('size');

        $attributeTypes = $this
            ->getFromTestContainer('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->getAttributeTypeByCodes(['color', 'size']);

        $this->assertEquals(
            ['color' => 'pim_catalog_identifier', 'size' => 'pim_catalog_text'],
            $attributeTypes
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributeWithAllValues(string $code)
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_identifier',
                'group' => 'other',
                'localizable' => true,
                'scopable' => true,
                'decimals_allowed' => true,
                'metric_family' => 'Length',
                'default_metric_unit' => 'METER',
                'available_locales' => ['en_US', 'fr_FR'],
                'labels' => [
                    'en_US' => 'An attribute',
                    'fr_FR' => 'Un attribut',
                ],
            ]
        );

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);

        return $attribute->getId();
    }

    private function createAttributeWithMinimumValues(string $code)
    {
        $attribute = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_text',
                'group' => 'other',
            ]
        );

        $this->getFromTestContainer('pim_catalog.saver.attribute')->save($attribute);

        return $attribute->getId();
    }
}
