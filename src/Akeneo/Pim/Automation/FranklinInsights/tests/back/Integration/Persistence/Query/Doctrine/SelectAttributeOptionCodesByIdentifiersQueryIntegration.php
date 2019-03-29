<?php

declare (strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Query\SelectAttributeOptionCodesByIdentifiersQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface as PimAttribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption as PimAttributeOption;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class SelectAttributeOptionCodesByIdentifiersQueryIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var SaverInterface */
    private $attributeOptionSaver;

    /** @var SelectAttributeOptionCodesByIdentifiersQueryInterface */
    private $selectAttributeOptionCodesByIdentifiersQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->getFromTestContainer('pim_catalog.saver.attribute');
        $this->attributeOptionSaver = $this->getFromTestContainer('pim_catalog.saver.attribute_option');
        $this->selectAttributeOptionCodesByIdentifiersQuery = $this->getFromTestContainer(
            'akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_attribute_option_codes_by_identifiers'
        );
    }

    public function test_it_finds_attribute_option_codes_for_an_attribute(): void
    {
        $attributeOptionCodes = $this->selectAttributeOptionCodesByIdentifiersQuery->execute(
            'router',
            ['color', 'size']
        );
        $this->assertEmpty($attributeOptionCodes);

        $attribute = $this->createAttribute('router');
        $this->createAttributeOption('color', $attribute);
        $this->createAttributeOption('size', $attribute);
        $this->createAttributeOption('width', $attribute);
        $this->createAttributeOption('color', $this->createAttribute('shoes'));

        $attributeOptionCodes = $this->selectAttributeOptionCodesByIdentifiersQuery->execute(
            'router',
            ['color', 'size']
        );
        $expectedAttributeOptionCodes = ['color', 'size'];
        $this->assertEquals($attributeOptionCodes, $expectedAttributeOptionCodes);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createAttributeOption(string $attributeOptionCode, PimAttribute $attribute): PimAttributeOption
    {
        $attributeOption = new PimAttributeOption();
        $attributeOption->setCode($attributeOptionCode);
        $attributeOption->setAttribute($attribute);

        $this->attributeOptionSaver->save($attributeOption);

        return $attributeOption;
    }

    private function createAttribute(string $attributeCode): PimAttribute
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
            ]
        );

        $this->attributeSaver->save($attribute);

        return $attribute;
    }
}
