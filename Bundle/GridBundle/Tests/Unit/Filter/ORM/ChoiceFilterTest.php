<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;
use Doctrine\Common\Collections\ArrayCollection;

class ChoiceFilterTest extends FilterTestCase
{
    const TEST_DOMAIN = 'someDomain';

    /**
     * @var ChoiceFilter
     */
    protected $model;

    /**
     * @var array
     */
    protected $testChoices = array('key1' => 'value1', 'key2' => 'value2');

    /**
     * @return ChoiceFilter
     */
    protected function createTestFilter()
    {
        return new ChoiceFilter($this->getTranslatorMock());
    }

    /**
     * @return array
     */
    public function getOperatorDataProvider()
    {
        return array(
            array(ChoiceFilterType::TYPE_CONTAINS, 'IN'),
            array(ChoiceFilterType::TYPE_NOT_CONTAINS, 'NOT IN'),
            array(false, 'IN')
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
            'empty_array_value' => array(
                'data' => array('value' => array()),
                'expectProxyQueryCalls' => array()
            ),
            'empty_collection_value' => array(
                'data' => array('value' => new ArrayCollection()),
                'expectProxyQueryCalls' => array()
            ),
            'zero_value' => array(
                'data' => array('value' => 0),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->in(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_choices'
                            )
                        ),
                        null
                    ),
                    array(
                        'setParameter',
                        array(self::TEST_NAME . '_choices', array(0)),
                        null
                    )
                )
            ),
            'contains' => array(
                'data' => array('value' => 'test', 'type' => ChoiceFilterType::TYPE_CONTAINS),
                'expectProxyQueryCalls' => array(
                    array(
                        'andHaving',
                        array(
                            $this->getExpressionFactory()->in(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_choices'
                            )
                        ),
                        null
                    ),
                    array(
                        'setParameter',
                        array(self::TEST_NAME . '_choices', array('test')),
                        null
                    )
                ),
                array(
                    'field_mapping' => array('filterByHaving' => true)
                )
            ),
            'not_contains' => array(
                'data' => array(
                    'value' => new ArrayCollection(array('test')),
                    'type'  => ChoiceFilterType::TYPE_NOT_CONTAINS
                ),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array(
                            $this->getExpressionFactory()->notIn(
                                self::TEST_NAME,
                                ':' . self::TEST_NAME . '_choices'
                            )
                        ),
                        null
                    ),
                    array(
                        'setParameter',
                        array(self::TEST_NAME . '_choices', array('test')),
                        null
                    )
                ),
                array(
                    'field_mapping' => array('fieldExpression' => self::TEST_NAME, 'filterByWhere' => true)
                )
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(
            array(
                'form_type' => ChoiceFilterType::NAME
            ),
            $this->model->getDefaultOptions()
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
                array(ChoiceFilterType::NAME,
                    array(
                        'show_filter' => false
                    )
                )
            ),
            'single select' => array(
                array('choices' => $this->testChoices),
                array(ChoiceFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'choices' => $this->testChoices
                        )
                    )
                )
            ),
            'multiple select' => array(
                array('choices' => $this->testChoices, 'multiple' => true, 'translation_domain' => self::TEST_DOMAIN),
                array(ChoiceFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'choices'  => $this->testChoices,
                            'multiple' => true
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
}
