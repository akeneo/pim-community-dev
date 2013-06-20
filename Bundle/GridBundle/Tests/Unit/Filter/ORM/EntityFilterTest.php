<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\EntityFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\EntityFilterType;

class EntityFilterTest extends FilterTestCase
{
    const TEST_CLASS          = 'Test:Class';
    const TEST_PROPERTY       = 'name';
    const TEST_QUERY_BUILDER  = 'test_query_builder';
    const TEST_GROUP_BY       = 'entity.type';
    const TEST_ENTITY_MANAGER = 'test_entity_manager';

    /**
     * @var EntityFilter
     */
    protected $model;

    /**
     * @return EntityFilter
     */
    protected function createTestFilter()
    {
        return new EntityFilter($this->getTranslatorMock());
    }

    /**
     * @return array
     */
    public function filterDataProvider()
    {
        // simple test just be sure that filter method works
        return array(
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array()
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(
            array(
                'form_type' => EntityFilterType::NAME
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
                array(
                    EntityFilterType::NAME,
                    array(
                        'show_filter' => false
                    )
                )
            ),
            'entity parameters' => array(
                array(
                    'multiple'      => true,
                    'class'         => self::TEST_CLASS,
                    'property'      => self::TEST_PROPERTY,
                    'group_by'      => self::TEST_GROUP_BY,
                    'query_builder' => self::TEST_QUERY_BUILDER,
                    'em'            => self::TEST_ENTITY_MANAGER,
                ),
                array(
                    EntityFilterType::NAME,
                    array(
                        'show_filter'   => false,
                        'field_options' => array(
                            'multiple'      => true,
                            'class'         => self::TEST_CLASS,
                            'property'      => self::TEST_PROPERTY,
                            'group_by'      => self::TEST_GROUP_BY,
                            'query_builder' => self::TEST_QUERY_BUILDER,
                            'em'            => self::TEST_ENTITY_MANAGER,
                        )
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
