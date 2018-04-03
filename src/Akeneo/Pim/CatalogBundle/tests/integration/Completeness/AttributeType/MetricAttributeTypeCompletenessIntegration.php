<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Completeness\AttributeType;

use Pim\Component\Catalog\AttributeTypes;

/**
 * Checks that the completeness has been well calculated for metric attribute type.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricAttributeTypeCompletenessIntegration extends AbstractCompletenessPerAttributeTypeTestCase
{
    public function testCompleteMetric()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_metric',
            AttributeTypes::METRIC
        );

        $this->configureMetricFamilyForAttribute('a_metric', 'Length');

        $productComplete = $this->createProductWithStandardValues(
            $family,
            'product_complete',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => 12, 'unit' => 'METER'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productComplete);

        $productAmountZero = $this->createProductWithStandardValues(
            $family,
            'product_amount_zero',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => 0, 'unit' => 'METER'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertComplete($productAmountZero);
    }

    public function testNotCompleteMetric()
    {
        $family = $this->createFamilyWithRequirement(
            'another_family',
            'ecommerce',
            'a_metric',
            AttributeTypes::METRIC
        );

        $this->configureMetricFamilyForAttribute('a_metric', 'Length');

        $productDataNull = $this->createProductWithStandardValues(
            $family,
            'product_data_null',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => null,
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productDataNull);
        $this->assertMissingAttributeForProduct($productDataNull, ['a_metric']);

        $productAmountNull = $this->createProductWithStandardValues(
            $family,
            'product_amount_null',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => null, 'unit' => 'METER'],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productAmountNull);
        $this->assertMissingAttributeForProduct($productAmountNull, ['a_metric']);

        $productAmountAndUnitNull = $this->createProductWithStandardValues(
            $family,
            'product_amount_and_unit_null',
            [
                'values' => [
                    'a_metric' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => ['amount' => null, 'unit' => null],
                        ],
                    ],
                ],
            ]
        );
        $this->assertNotComplete($productAmountAndUnitNull);
        $this->assertMissingAttributeForProduct($productAmountAndUnitNull, ['a_metric']);
    }

    /**
     * @param string $code
     * @param string $metricFamily
     */
    private function configureMetricFamilyForAttribute($code, $metricFamily)
    {
        $metric = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($code);
        $metric->setMetricFamily($metricFamily);
        $this->get('pim_catalog.saver.attribute')->save($metric);
    }
}
