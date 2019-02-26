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

namespace AkeneoTestEnterprise\Asset\Integration\Persistence\Query;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FindFamilyCodesWhereAttributesAreRequiredIntegration extends TestCase
{
    public function testFindFamilyCodesWhereAttributesAreRequired()
    {
        $query = $this->get('pimee_product_asset.query.family_codes_where_attributes_are_required');
        Assert::assertEquals(['family_A', 'family_B'], $query->find(['attribute_A', 'attribute_B']));
        Assert::assertEquals(['family_A'], $query->find(['attribute_B']));
        Assert::assertEquals([], $query->find(['attribute_C', 'attribute_D']));
        Assert::assertEquals(
            ['family_A', 'family_B'],
            $query->find(['attribute_A', 'attribute_B', 'attribute_C', 'attribute_D'])
        );
        Assert::assertEquals([], $query->find([]));
    }

    /**
     * Family_A
     *      attribute_A Required
     *      attribute_B Required
     *      attribute_C
     *
     * Family_B
     *      attribute_A Required
     *      attribute_B
     *
     * Family_C
     *      attribute_A
     *      attribute_B
     *      attribute_C
     *      attribute_D
     */
    private function loadFixtures(): void
    {
        $attributeA = $this->get('pim_catalog.factory.attribute')->create();
        $attributeB = $this->get('pim_catalog.factory.attribute')->create();
        $attributeC = $this->get('pim_catalog.factory.attribute')->create();
        $attributeD = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update(
            $attributeA,
            [
                'code' => 'attribute_A',
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );
        $this->get('pim_catalog.updater.attribute')->update(
            $attributeB,
            [
                'code' => 'attribute_B',
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );
        $this->get('pim_catalog.updater.attribute')->update(
            $attributeC,
            [
                'code' => 'attribute_C',
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );
        $this->get('pim_catalog.updater.attribute')->update(
            $attributeD,
            [
                'code' => 'attribute_D',
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );
        $this->get('pim_catalog.saver.attribute')->saveAll([$attributeA, $attributeB, $attributeC, $attributeD]);

        $familyA = $this->get('pim_catalog.factory.family')->create();
        $familyB = $this->get('pim_catalog.factory.family')->create();
        $familyC = $this->get('pim_catalog.factory.family')->create();
        $this->get('pim_catalog.updater.family')->update(
            $familyA,
            [
                'code' => 'family_A',
                'attributes' => ['attribute_A', 'attribute_B', 'attribute_C'],
                'attribute_requirements' => [
                    'ecommerce' => ['attribute_A', 'attribute_B'],
                ],
            ]
        );
        $this->get('pim_catalog.updater.family')->update(
            $familyB,
            [
                'code' => 'family_B',
                'attributes' => ['attribute_A', 'attribute_B'],
                'attribute_requirements' => [
                    'ecommerce' => ['attribute_A'],
                ],
            ]
        );
        $this->get('pim_catalog.updater.family')->update(
            $familyC,
            [
                'code' => 'family_C',
                'attributes' => ['attribute_A', 'attribute_B', 'attribute_C', 'attribute_D'],
                'attribute_requirements' => [],
            ]
        );
        $this->get('pim_catalog.saver.family')->saveAll([$familyA, $familyB, $familyC]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
