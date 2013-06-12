<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

use Pim\Bundle\FilterBundle\Form\Type\Filter\CurrencyFilterType;
use Pim\Bundle\GridBundle\Filter\ORM\CurrencyFilter;
use Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\FilterTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyFilterTest extends FilterTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function createTestFilter()
    {
        return new CurrencyFilter($this->getTranslatorMock());
    }

    /**
     * Data provider for operator
     *
     * @return multitype:multitype:boolean string  multitype:string number
     *
     * @static
     */
    public static function getOperatorDataProvider()
    {
        return array(
            'operator_greater_equals' => array(CurrencyFilterType::TYPE_GREATER_EQUAL, '>='),
            'operator_greater_than'   => array(CurrencyFilterType::TYPE_GREATER_THAN, '>'),
            'operator_equal'          => array(CurrencyFilterType::TYPE_EQUAL, '='),
            'operator_less_equal'     => array(CurrencyFilterType::TYPE_LESS_EQUAL, '<='),
            'operator_less_than'      => array(CurrencyFilterType::TYPE_LESS_THAN, '<'),
            'operator_false'          => array(false, '=')
        );
    }

    /**
     * Test related method
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
        $this->assertEquals(array('form_type' => CurrencyFilterType::NAME), $this->model->getDefaultOptions());
    }

    /**
     * {@inheritdoc}
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
            'no_currency' => array(
                'data' => array('value' => 5),
                'expectProxyQueryCalls' => array()
            ),
            'not_alphanumeric_currency' => array(
                'data' => array('value' => 5, 'currency' => 5),
                'expectProxyQueryCalls' => array()
            ),
//             'equals' => array(
//                 'data' => array('value' => 25, 'currency' => 'EUR', 'type' => '='),
//                 'expectProxyQueryCalls' => array(
//                     array('getUniqueParameterId', array(), 'p1'),
//                     array('andWhere',
//                         array(
//                             $this->getExpressionFactory()->eq(
//                                 self::TEST_ALIAS .'.'. self::TEST_FIELD,
//                                 ':'. self::TEST_NAME .'_p1'
//                             )
//                         ), null),
//                     array('setParameter', array(self::TEST_NAME .'_p1', 25), null)
//                 )
//             )
        );
    }

    /**
     * Data provider for render settings
     *
     * @return array
     * @static
     */
    public static function getRenderSettingsDataProvider()
    {
        return array(
            'default' => array(
                array(),
                array(CurrencyFilterType::NAME,
                    array(
                        'show_filter' => false,
                        'data_type' => CurrencyFilterType::DATA_DECIMAL
                    )
                )
            ),
            'integer' => array(
                array('data_type' => FieldDescriptionInterface::TYPE_INTEGER),
                array(CurrencyFilterType::NAME,
                    array(
                        'show_filter' => false,
                        'data_type' => CurrencyFilterType::DATA_INTEGER
                    )
                )
            ),
            'decimal' => array(
                array('data_type' => FieldDescriptionInterface::TYPE_DECIMAL),
                array(CurrencyFilterType::NAME,
                    array(
                        'show_filter' => false,
                        'data_type' => CurrencyFilterType::DATA_DECIMAL
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
