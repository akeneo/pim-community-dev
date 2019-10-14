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
            ->get('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('size');
        $this->assertNull($attribute);

        $this->createAttributeWithAllValues('size');

        $attribute = $this
            ->get('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('size');

        $expectedAttribute = new Attribute(
            new AttributeCode('size'),
            'pim_catalog_metric',
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

        $this->createAttributeWithMinimumValues('size2');
        $attribute = $this
            ->get('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('size2');

        $expectedAttribute = new Attribute(
            new AttributeCode('size2'),
            'pim_catalog_metric',
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
            ->get('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findOneByIdentifier('color');
        $this->assertEmpty($attributes);

        $this->createAttributeWithAllValues('color');
        $this->createAttributeWithMinimumValues('size');

        $attributes = $this
            ->get('akeneo.pim.automation.franklin_insights.repository.attribute')
            ->findByCodes(['color', 'size']);
        $this->assertCount(2, $attributes);
        $this->assertContainsOnlyInstancesOf(Attribute::class, $attributes);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributeWithAllValues(string $code)
    {
        $attribute = $this->get('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_metric',
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

        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute->getId();
    }

    private function createAttributeWithMinimumValues(string $code)
    {
        $attribute = $this->get('akeneo_ee_integration_tests.builder.attribute')->build(
            [
                'code' => $code,
                'type' => 'pim_catalog_metric',
                'group' => 'other',
            ]
        );

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }
}
