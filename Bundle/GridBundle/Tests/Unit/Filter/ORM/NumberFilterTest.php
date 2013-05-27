<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;

class NumberFilterTest extends FilterTestCase
{
    /**
     * @var NumberFilter
     */
    protected $model;

    protected function createTestFilter()
    {
        return new NumberFilter($this->getTranslatorMock());
    }

    /**
     * @return array
     */
    public function getOperatorDataProvider()
    {
        return array(
            array(NumberFilterType::TYPE_GREATER_EQUAL, '>='),
            array(NumberFilterType::TYPE_GREATER_THAN, '>'),
            array(NumberFilterType::TYPE_EQUAL, '='),
            array(NumberFilterType::TYPE_LESS_EQUAL, '<='),
            array(NumberFilterType::TYPE_LESS_THAN, '<'),
            array(false, '=')
        );
    }

    /**
     * @dataProvider getOperatorDataProvider
     *
     * @param mixed $type
     * @param string $expected
     */
    public function testGetOperator($type, $expected)
    {
        $this->assertEquals($expected, $this->model->getOperator($type));
    }

    /**
     * @return array
     */
    public function filterDataProvider()
    {
        return array(
            'not_array_value' => array(
                'data' => '',
                'expectProxyQueryCalls' => array()
            ),
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array()
            ),
            'no_value' => array(
                'data' => array('value' => ''),
                'expectProxyQueryCalls' => array()
            ),
            'not_numeric' => array(
                'data' => array('value' => 'abc'),
                'expectProxyQueryCalls' => array()
            ),
            'equals' => array(
                'data' => array('value' => 123, 'type' => NumberFilterType::TYPE_EQUAL),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('andWhere',
                        array(
                            $this->getExpressionFactory()->eq(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p1'
                            )
                        ), null),
                    array('setParameter', array(self::TEST_NAME . '_p1', 123), null)
                )
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(array('form_type' => NumberFilterType::NAME), $this->model->getDefaultOptions());
    }

    /**
     * @return array
     */
    public function getRenderSettingsDataProvider()
    {
        return array(
            'default' => array(
                array(),
                array(NumberFilterType::NAME,
                    array(
                        'show_filter' => false,
                        'data_type' => NumberFilterType::DATA_INTEGER
                    )
                )
            ),
            'integer' => array(
                array('data_type' => FieldDescriptionInterface::TYPE_INTEGER),
                array(NumberFilterType::NAME,
                    array(
                        'show_filter' => false,
                        'data_type' => NumberFilterType::DATA_INTEGER
                    )
                )
            ),
            'decimal' => array(
                array('data_type' => FieldDescriptionInterface::TYPE_DECIMAL),
                array(NumberFilterType::NAME,
                    array(
                        'show_filter' => false,
                        'data_type' => NumberFilterType::DATA_DECIMAL
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider getRenderSettingsDataProvider
     */
    public function testGetRenderSettings($options, $expectedRenderSettings)
    {
        $this->model->initialize(self::TEST_NAME, $options);
        $this->assertEquals($expectedRenderSettings, $this->model->getRenderSettings());
    }
}
