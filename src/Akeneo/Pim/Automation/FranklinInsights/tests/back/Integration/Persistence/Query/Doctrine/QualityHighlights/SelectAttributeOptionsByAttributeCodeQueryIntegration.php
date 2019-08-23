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

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeOptionsByAttributeCodeQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class SelectAttributeOptionsByAttributeCodeQueryIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var SaverInterface */
    private $attributeOptionSaver;

    /** @var SelectAttributeOptionsByAttributeCodeQueryInterface */
    private $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->getFromTestContainer('pim_catalog.saver.attribute');
        $this->attributeOptionSaver = $this->getFromTestContainer('pim_catalog.saver.attribute_option');
        $this->query = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_attributes_options_by_attribute_code');
    }

    public function test_it_finds_attribute_options_for_an_attribute(): void
    {
        $attribute = $this->createAttribute('color');
        $this->createBlueAttributeOption($attribute);
        $this->createRedAttributeOption($attribute);

        $options = $this->query->execute('color');

        $expectedOptions = [
            [
                'code' => 'blue',
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Blue',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'label' => 'Bleu',
                    ],
                ],
            ],
            [
                'code' => 'red',
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Red',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'label' => 'Rouge',
                    ],
                ],
            ],
        ];

        $this->assertSame($expectedOptions, $options);
    }

    private function createBlueAttributeOption(AttributeInterface $attribute): AttributeOption
    {
        $optionValueEn = new AttributeOptionValue();
        $optionValueEn->setLocale('en_US');
        $optionValueEn->setLabel('Blue');
        $optionValueFr = new AttributeOptionValue();
        $optionValueFr->setLocale('fr_FR');
        $optionValueFr->setLabel('Bleu');

        $attributeOption = new AttributeOption();
        $attributeOption->setCode('blue');
        $attributeOption->setAttribute($attribute);
        $attributeOption->addOptionValue($optionValueEn);
        $attributeOption->addOptionValue($optionValueFr);

        $this->attributeOptionSaver->save($attributeOption);

        return $attributeOption;
    }

    private function createRedAttributeOption(AttributeInterface $attribute): AttributeOption
    {
        $optionValueEn = new AttributeOptionValue();
        $optionValueEn->setLocale('en_US');
        $optionValueEn->setLabel('Red');
        $optionValueFr = new AttributeOptionValue();
        $optionValueFr->setLocale('fr_FR');
        $optionValueFr->setLabel('Rouge');

        $attributeOption = new AttributeOption();
        $attributeOption->setCode('red');
        $attributeOption->setAttribute($attribute);
        $attributeOption->addOptionValue($optionValueEn);
        $attributeOption->addOptionValue($optionValueFr);

        $this->attributeOptionSaver->save($attributeOption);

        return $attributeOption;
    }

    private function createAttribute(string $attributeCode): AttributeInterface
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
