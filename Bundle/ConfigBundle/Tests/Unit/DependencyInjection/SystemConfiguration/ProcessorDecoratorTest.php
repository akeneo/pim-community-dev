<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\DependencyInjection\SystemConfiguration;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

class ProcessorDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ProcessorDecorator */
    protected $processor;

    public function setUp()
    {
        $this->processor = new ProcessorDecorator();
    }

    public function tearDown()
    {
        unset($this->processor);
    }

    /**
     * @dataProvider mergeDataProvider
     *
     * @param array $startData
     * @param array $newData
     * @param array $expectedResult
     */
    public function testMerge($startData, $newData, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->processor->merge($startData, $newData));
    }

    /**
     * @return array
     */
    public function mergeDataProvider()
    {
        return array(
            'merge tree test'   => array(
                array(
                    ProcessorDecorator::ROOT => array(
                        ProcessorDecorator::TREE_ROOT => array('group1' => array('group2' => array('field'))),
                    )
                ),
                array(
                    ProcessorDecorator::ROOT => array(
                        ProcessorDecorator::TREE_ROOT => array('group1' => array('group2' => array('field2'))),
                    )
                ),
                array(
                    ProcessorDecorator::ROOT => array(
                        ProcessorDecorator::TREE_ROOT => array('group1' => array('group2' => array('field', 'field2'))),
                    )
                ),
            ),
            'merge fields test' => array(
                array(
                    ProcessorDecorator::ROOT => array(
                        ProcessorDecorator::FIELDS_ROOT => array(
                            'someFieldName' => array(
                                'label'   => 'testLabel1',
                                'options' => array()
                            )
                        ),
                    )
                ),
                array(
                    ProcessorDecorator::ROOT => array(
                        ProcessorDecorator::FIELDS_ROOT => array(
                            'someFieldName' => array(
                                'label' => 'overrideLabel',
                            ),
                            'newField'      => array(
                                'label'   => 'testLabel2',
                                'options' => array()
                            )
                        ),
                    )
                ),
                array(
                    ProcessorDecorator::ROOT => array(
                        ProcessorDecorator::FIELDS_ROOT => array(
                            'someFieldName' => array(
                                'label'   => 'overrideLabel',
                                'options' => array()
                            ),
                            'newField'      => array(
                                'label'   => 'testLabel2',
                                'options' => array()
                            )
                        ),
                    )
                ),
            ),
        );
    }
}
