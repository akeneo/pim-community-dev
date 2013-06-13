<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Pim\Bundle\FilterBundle\Form\Type\Filter\CategoryFilterType;
use Pim\Bundle\GridBundle\Filter\ORM\CategoryFilter;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\FilterTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryFilterTest extends FilterTestCase
{

    /**
     * @var CategoryFilter
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    protected function createTestFilter()
    {
        return new CategoryFilter($this->getTranslatorMock());
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
            'operator_greater_equals' => array(CategoryFilterType::TYPE_CONTAINS, 'IN'),
            'operator_greater_than'   => array(CategoryFilterType::TYPE_NOT_CONTAINS, 'NOT IN'),
            'operator_equal'          => array(CategoryFilterType::TYPE_UNCLASSIFIED, 'UNCLASSIFIED'),
            'operator_less_equal'     => array(CategoryFilterType::TYPE_CLASSIFIED, 'CLASSIFIED'),
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
                'form_type' => CategoryFilterType::NAME,
                'mapped_property' => 'id'
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
            )
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
                array(CategoryFilterType::NAME,
                    array(
                        'show_filter' => false
                    )
                )
            ),
            'single select' => array(
                array('choices' => array()),
                array(CategoryFilterType::NAME,
                    array(
                        'show_filter'   => false
                    )
                )
            ),
            'multiple select' => array(
                array('multiple' => true),
                array(CategoryFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
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
