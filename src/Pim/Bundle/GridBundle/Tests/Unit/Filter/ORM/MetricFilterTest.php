<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\FilterTestCase;
use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;
use Pim\Bundle\GridBundle\Filter\ORM\MetricFilter;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilterTest extends FilterTestCase
{
    /**
     * @var MetricFilter
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function createTestFilter()
    {
        return new MetricFilter($this->getTranslatorMock(), $this->getMeasureConverterMock());
    }

    /**
     * Get measure converter mock
     * @return \Oro\Bundle\MeasureBundle\Convert\MeasureConverter
     */
    protected function getMeasureConverterMock()
    {
        $measureConfig = $this->initializeMeasureConfig();
        $measureConverter = new MeasureConverter(array('measures_config' => $measureConfig));

        return $measureConverter;
    }

    /**
     * Initialize config
     * @return array
     */
    protected function initializeMeasureConfig()
    {
        return array(
            'Weight' => array(
                'standard' => 'KILOGRAM',
                'units'    => array(
                    'GRAM'     => array(
                        'convert' => array(array('mul' => 0.001)),
                        'symbol'  => 'g'
                    ),
                    'KILOGRAM' => array(
                        'convert' => array(array('mul' => 1)),
                        'symbol'  => 'kg'
                    )
                )
            )
        );
    }

    /**
     * Data provider for operator
     *
     * @return array
     *
     * @static
     */
    public static function getOperatorDataProvider()
    {
        return array(
            'operator_greater_equals' => array(MetricFilterType::TYPE_GREATER_EQUAL, '>='),
            'operator_greater_than'   => array(MetricFilterType::TYPE_GREATER_THAN, '>'),
            'operator_equal'          => array(MetricFilterType::TYPE_EQUAL, '='),
            'operator_less_equal'     => array(MetricFilterType::TYPE_LESS_EQUAL, '<='),
            'operator_less_than'      => array(MetricFilterType::TYPE_LESS_THAN, '<'),
            'operator_false'          => array(false, '=')
        );
    }

    /**
     * Test related method
     *
     * @param mixed  $type     operator
     * @param string $expected result expected
     *
     * @dataProvider getOperatorDataProvider
     */
    public function testGetOperator($type, $expected)
    {
        $this->assertEquals($expected, $this->model->getOperator($type));
    }

    /**
     * Test related method
     */
    public function testGetDefaultOptions()
    {
        $this->assertEquals(
            array('form_type' => MetricFilterType::NAME),
            $this->model->getDefaultOptions()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filterDataProvider()
    {
        return array(
            'not_array_value' => array(
                'data' => '',
                'expectProxyQueryCalls' => array(),
                array('field_options' => array('family' => 'Weight'))
            ),
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array(),
                array('field_options' => array('family' => 'Weight'))
            ),
            'no_value' => array(
                'data' => array('value' => ''),
                'expectProxyQueryCalls' => array(),
                array('field_options' => array('family' => 'Weight'))
            ),
            'not_numeric' => array(
                'data' => array('value' => 'abc'),
                'expectProxyQueryCalls' => array(),
                array('field_options' => array('family' => 'Weight'))
            ),
            'no_unit' => array(
                'data' => array('value' => 5),
                'expectProxyQueryCalls' => array(),
                array('field_options' => array('family' => 'Weight'))
            ),
            'not_string_unit' => array(
                'data' => array('value' => 10, 'unit' => 1),
                'expectProxyQueryCalls' => array(),
                array('field_options' => array('family' => 'Weight'))
            ),
            'valid_data' => array(
                'data' => array('value' => 10, 'unit' => 'GRAM'),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array(
                        'andWhere',
                        array($this->getExpressionFactory()->eq('valueMetrics.baseData', ':'. self::TEST_NAME .'_p1')),
                        null
                    ),
                    array('setParameter', array(self::TEST_NAME .'_p1', '0.010'), null)
                ),
                array('field_options' => array('family' => 'Weight'))
            )
        );
    }

    /**
     * Data provider for getRenderSettings method
     * @return array
     */
    public function getRenderSettingsDataProvider()
    {
        return array(
            'default' => array(
                array('field_options' => array('family' => 'Weight')),
                array(
                    MetricFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'data_type'     => MetricFilterType::DATA_DECIMAL,
                        'field_options' => array('family' => 'Weight')
                    )
                )
            )
        );
    }

    /**
     * Test related method
     * @param array $options
     * @param array $expectedRenderSettings
     *
     * @dataProvider getRenderSettingsDataProvider
     */
    public function testGetRenderSettings($options, $expectedRenderSettings)
    {
        $this->model->initialize(self::TEST_NAME, $options);
        $this->assertEquals($expectedRenderSettings, $this->model->getRenderSettings());
    }
}
