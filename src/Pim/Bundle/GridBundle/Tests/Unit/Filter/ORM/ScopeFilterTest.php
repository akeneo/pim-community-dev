<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\FilterTestCase;
use Pim\Bundle\FilterBundle\Form\Type\Filter\ScopeFilterType;
use Pim\Bundle\GridBundle\Filter\ORM\ScopeFilter;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopeFilterTest extends FilterTestCase
{
    /**
     * @var ScopeFilter
     */
    protected $model;

    /**
     * @var array
     */
    protected $testChoices = array('ecommerce' => 'E-Commerce', 'mobile' => 'Mobile');

    /**
     * {@inheritdoc}
     */
    protected function createTestFilter()
    {
        return new ScopeFilter($this->getTranslatorMock());
    }

    /**
     * Data provider for operator
     *
     * @return multitype:multitype:boolean string  multitype:string number
     */
    public static function getOperatorDataProvider()
    {
        return array(
            'operator_greater_equals' => array(ScopeFilterType::TYPE_CONTAINS, 'IN'),
            'operator_greater_than'   => array(ScopeFilterType::TYPE_NOT_CONTAINS, 'NOT IN'),
            'operator_false'          => array(false, 'IN')
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
        $this->assertEquals(
            array(
                'form_type' => ScopeFilterType::NAME
            ),
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
            'empty_array_value' => array(
                'data' => array('value' => array()),
                'expectProxyQueryCalls' => array()
            ),
            'numeric' => array(
                'data' => array('value' => 4),
                'expectProxyQueryCalls' => array()
            )
        );
    }

    /**
     * @return array
     */
    public function getRenderSettingsDataProvider()
    {
        return array(
            'default' => array(
                array(),
                array(ScopeFilterType::NAME,
                    array(
                        'show_filter' => false
                    )
                )
            ),
            'single select' => array(
                array('choices' => $this->testChoices),
                array(ScopeFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'choices' => $this->testChoices
                        )
                    )
                )
            ),
            'multiple select' => array(
                array('choices' => $this->testChoices, 'multiple' => true),
                array(ScopeFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'choices'  => $this->testChoices,
                            'multiple' => true
                        )
                    )
                )
            ),
        );
    }

    /**
     * Test related method
     *
     * @param array $options                options passed to filter
     * @param array $expectedRenderSettings expected result
     *
     * @dataProvider getRenderSettingsDataProvider
     */
    public function testGetRenderSettings($options, $expectedRenderSettings)
    {
        $this->model->initialize(self::TEST_NAME, $options);
        $this->assertEquals($expectedRenderSettings, $this->model->getRenderSettings());
    }
}
