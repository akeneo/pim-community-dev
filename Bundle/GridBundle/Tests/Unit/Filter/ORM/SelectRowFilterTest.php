<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\SelectRowFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\SelectRowFilter;

class SelectRowFilterTest extends FilterTestCase
{
    const TEST_DOMAIN = 'SomeDomain';

    /**
     * @var SelectRowFilter
     */
    protected $model;

    /**
     * @var array
     */
    protected $testChoices = array('key1' => 'value1', 'key2' => 'value2');

    /**
     * @return SelectRowFilter
     */
    protected function createTestFilter()
    {
        return new SelectRowFilter($this->getTranslatorMock());
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
            'bad case to sort' => array(
                'data' => array('value' => SelectRowFilter::SELECTED_VALUE, 'in' => array(), 'out' => array()),
                'expectProxyQueryCalls' => array()
            ),
            'empty out set should return all data' => array(
                'data' => array('value' => SelectRowFilter::SELECTED_VALUE, 'in' => null, 'out' => array()),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->eq(1, 1)
                        ),
                        null
                    ),
                )
            ),
            'empty in set should return empty dataset' => array(
                'data' => array('value' => SelectRowFilter::SELECTED_VALUE, 'out' => null, 'in' => array()),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->eq(0, 1)
                        ),
                        null
                    ),
                )
            ),
            'some data in inset sould add "where in" statement' => array(
                'data' => array('value' => SelectRowFilter::SELECTED_VALUE, 'out' => null, 'in' => '1,2,3'),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->in(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                array('1', '2', '3')
                            )
                        ),
                        null
                    ),
                )
            ),
            'some data in outset sould add "where not in" statement' => array(
                'data' => array('value' => SelectRowFilter::SELECTED_VALUE, 'in' => null, 'out' => '1,2,3'),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->notIn(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                array('1', '2', '3')
                            )
                        ),
                        null
                    ),
                )
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(
            array(
                'form_type' => SelectRowFilterType::NAME
            ),
            $this->model->getDefaultOptions()
        );
    }

    /**
     * @return array
     */
    public function getRenderSettingsDataProvider()
    {
        $defaultChoices = array(
            SelectRowFilter::NOT_SELECTED_VALUE => 'Not selected',
            SelectRowFilter::SELECTED_VALUE     => 'Selected'
        );

        $testChoices = array(0 => 'Some choice');

        return array(
            'default' => array(
                array(),
                array(SelectRowFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'choices'    => $defaultChoices,
                            'multiple'   => false
                        )
                    )
                )
            ),
            'test passing choices' => array(
                array('choices' => $testChoices),
                array(SelectRowFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'choices' => $testChoices,
                            'multiple'   => false
                        )
                    )
                )
            ),
            'multiple select not allowed' => array(
                array('multiple' => true, 'show_filter' => true, 'translation_domain' => self::TEST_DOMAIN),
                array(SelectRowFilterType::NAME,
                    array(
                        'show_filter'   => true,
                        'field_options' => array(
                            'choices'    => $defaultChoices,
                            'multiple'   => false
                        ),
                        'translation_domain' => self::TEST_DOMAIN
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

    public function testNeedCollection()
    {
        $this->assertTrue($this->model->needCollection());
    }
}
