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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Integration\Persistence\Query\Doctrine\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SelectAttributesToApplyQueryIntegration extends TestCase
{
    /** @var EntityBuilder */
    private $attributeBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var SelectAttributesToApplyQueryInterface */
    private $query;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $attributeOptionSaver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attributeBuilder = $this->getFromTestContainer('akeneo_ee_integration_tests.builder.attribute');
        $this->attributeSaver = $this->getFromTestContainer('pim_catalog.saver.attribute');
        $this->attributeOptionSaver = $this->getFromTestContainer('pim_catalog.saver.attribute_option');
        $this->validator = $this->getFromTestContainer('validator');
        $this->query = $this->getFromTestContainer('akeneo.pim.automation.franklin_insights.infrastructure.persistence.query.select_attributes_to_apply');
    }

    public function test_it_returns_pim_attribute_code_exact_match_on_code()
    {
        $attributeCode1 = $this->createTextAttribute('weight', ['en_US' => 'Weight', 'fr_FR' => 'Poids'])->getCode();
        $attributeCode2 = $this->createSimpleSelectAttribute('color')->getCode();
        $attributeCode3 = $this->createMetricAttribute('size')->getCode();
        $attributeCode4 = $this->createTextAttribute('attr_without_label', [])->getCode();

        $attributes = $this->query->execute([$attributeCode1, $attributeCode2, $attributeCode3, $attributeCode4]);

        $expectedResult = [
            [
                'code' => 'weight',
                'type' => AttributeTypes::TEXT,
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Weight',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'label' => 'Poids',
                    ],
                ],
            ],
            [
                'code' => 'color',
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Color',
                    ],
                    [
                        'locale' => 'fr_FR',
                        'label' => 'Couleur',
                    ],
                ],
            ],
            [
                'code' => 'size',
                'type' => AttributeTypes::METRIC,
                'metric_family' => 'Length',
                'unit' => 'INCHES',
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'Size',
                    ],
                ],
            ],
            [
                'code' => 'attr_without_label',
                'type' => AttributeTypes::TEXT,
                'labels' => [],
            ],
        ];

        $this->assertEqualsCanonicalizing($expectedResult, $attributes);
    }

    private function createTextAttribute(string $attributeCode, array $labels): AttributeInterface
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::TEXT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'labels' => $labels,
            ]
        );
        $this->validator->validate($attribute);
        $this->attributeSaver->save($attribute);

        return $attribute;
    }

    private function createSimpleSelectAttribute(string $attributeCode): AttributeInterface
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::OPTION_SIMPLE_SELECT,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'labels' => ['en_US' => 'Color', 'fr_FR' => 'Couleur'],
            ]
        );
        $this->validator->validate($attribute);
        $this->attributeSaver->save($attribute);

        return $attribute;
    }

    private function createMetricAttribute(string $attributeCode): AttributeInterface
    {
        $attribute = $this->attributeBuilder->build(
            [
                'code' => $attributeCode,
                'type' => AttributeTypes::METRIC,
                'group' => AttributeGroup::DEFAULT_GROUP_CODE,
                'labels' => ['en_US' => 'Size'],
                'decimals_allowed' => true,
                'metric_family' => 'Length',
                'default_metric_unit' => 'INCHES',
            ]
        );
        $this->validator->validate($attribute);
        $this->attributeSaver->save($attribute);

        return $attribute;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
