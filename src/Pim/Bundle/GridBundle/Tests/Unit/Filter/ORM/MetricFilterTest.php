<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Filter\ORM;

use Pim\Bundle\FilterBundle\Form\Type\Filter\MetricFilterType;

use Pim\Bundle\GridBundle\Filter\ORM\MetricFilter;

use Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\FilterTestCase;

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

    protected function getMeasureConverterMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\MeasureBundle\Convert\MeasureConverter')
            ->disableOriginalConstructor()
            ->getMock();

        $measureConfig = $this->getMeasureConfig();

        return new MeasureConverter($measureConfig);
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
            )
        );
    }
}
