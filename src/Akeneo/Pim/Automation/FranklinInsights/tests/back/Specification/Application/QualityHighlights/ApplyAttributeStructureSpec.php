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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\QualityHighlightsProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributeOptionsByAttributeCodeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Query\SelectAttributesToApplyQueryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApplyAttributeStructureSpec extends ObjectBehavior
{
    public function let(
        SelectAttributesToApplyQueryInterface $selectAttributesToApplyQuery,
        QualityHighlightsProviderInterface $qualityHighlightsProvider,
        SelectAttributeOptionsByAttributeCodeQueryInterface $selectAttributeOptions
    ) {
        $this->beConstructedWith($selectAttributesToApplyQuery, $qualityHighlightsProvider, $selectAttributeOptions);
    }

    public function it_applies_nothing_if_no_attributes($selectAttributesToApplyQuery, $qualityHighlightsProvider, $selectAttributeOptions)
    {
        $selectAttributesToApplyQuery->execute([1, 42])->willReturn([]);
        $selectAttributeOptions->execute(Argument::any())->shouldNotBeCalled();
        $qualityHighlightsProvider->applyAttributeStructure(Argument::any())->shouldNotBeCalled();

        $this->apply([1, 42]);
    }

    public function it_applies_new_attributes($selectAttributesToApplyQuery, $qualityHighlightsProvider, $selectAttributeOptions)
    {
        $attributeCode1 = 'color';
        $attributeCode2 = 'size';
        $selectAttributesToApplyQuery->execute([1, 42])->willReturn([
            $attributeCode1 => [
                'code' => $attributeCode1,
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
            $attributeCode2 => [
                'code' => $attributeCode2,
                'type' => AttributeTypes::METRIC,
                'metric_family' => 'length',
                'unit' => 'inches',
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'US size',
                    ],
                ],
            ]
        ]);

        $selectAttributeOptions->execute($attributeCode1)->willReturn([
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
        ]);
        $selectAttributeOptions->execute($attributeCode2)->shouldNotBeCalled();

        $expectedAppliedAttributes = [
            $attributeCode1 => [
                'code' => $attributeCode1,
                'type' => AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS[AttributeTypes::OPTION_SIMPLE_SELECT],
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
                'options' => [
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
                ],
            ],
            $attributeCode2 => [
                'code' => 'size',
                'type' => AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS[AttributeTypes::METRIC],
                'metric_family' => 'length',
                'unit' => 'inches',
                'labels' => [
                    [
                        'locale' => 'en_US',
                        'label' => 'US size',
                    ],
                ],
            ]
        ];

        $qualityHighlightsProvider->applyAttributeStructure($expectedAppliedAttributes)->shouldBeCalled();

        $this->apply([1, 42]);
    }
}
