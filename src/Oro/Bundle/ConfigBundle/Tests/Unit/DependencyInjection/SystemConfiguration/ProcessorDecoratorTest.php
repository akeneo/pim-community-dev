<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\DependencyInjection\SystemConfiguration;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

class ProcessorDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ProcessorDecorator */
    protected $processor;

    public function setUp(): void
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
        return [
            'merge tree test'   => [
                [
                    ProcessorDecorator::ROOT => [
                        ProcessorDecorator::TREE_ROOT => ['group1' => ['group2' => ['field']]],
                    ]
                ],
                [
                    ProcessorDecorator::ROOT => [
                        ProcessorDecorator::TREE_ROOT => ['group1' => ['group2' => ['field2']]],
                    ]
                ],
                [
                    ProcessorDecorator::ROOT => [
                        ProcessorDecorator::TREE_ROOT => ['group1' => ['group2' => ['field', 'field2']]],
                    ]
                ],
            ],
            'merge fields test' => [
                [
                    ProcessorDecorator::ROOT => [
                        ProcessorDecorator::FIELDS_ROOT => [
                            'someFieldName' => [
                                'label'   => 'testLabel1',
                                'options' => []
                            ]
                        ],
                    ]
                ],
                [
                    ProcessorDecorator::ROOT => [
                        ProcessorDecorator::FIELDS_ROOT => [
                            'someFieldName' => [
                                'label' => 'overrideLabel',
                            ],
                            'newField'      => [
                                'label'   => 'testLabel2',
                                'options' => []
                            ]
                        ],
                    ]
                ],
                [
                    ProcessorDecorator::ROOT => [
                        ProcessorDecorator::FIELDS_ROOT => [
                            'someFieldName' => [
                                'label'   => 'overrideLabel',
                                'options' => []
                            ],
                            'newField'      => [
                                'label'   => 'testLabel2',
                                'options' => []
                            ]
                        ],
                    ]
                ],
            ],
        ];
    }
}
