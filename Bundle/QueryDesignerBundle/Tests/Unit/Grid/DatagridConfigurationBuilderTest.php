<?php

namespace Oro\Bundle\QueryDesignerBundle\Tests\Unit\Grid;

use Oro\Bundle\QueryDesignerBundle\Grid\DatagridConfigurationBuilder;
use Oro\Bundle\QueryDesignerBundle\Tests\Unit\Fixtures\QueryDesignerModel;

class DatagridConfigurationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "columns" definition does not exist.
     */
    public function testEmpty()
    {
        $model = new QueryDesignerModel();
        $model->setDefinition(json_encode([]));
        new DatagridConfigurationBuilder('test_grid', $model, $this->getDoctrine());
    }

    /**
     * @expectedException \Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "columns" definition must not be empty.
     */
    public function testEmptyColumns()
    {
        $model = new QueryDesignerModel();
        $model->setDefinition(json_encode(['columns' => []]));
        new DatagridConfigurationBuilder('test_grid', $model, $this->getDoctrine());
    }

    public function testNoFilters()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $definition = [
            'columns' => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => '']
            ]
        ];
        $doctrine   = $this->getDoctrine(
            [
                $en => ['column1' => 'string']
            ]
        );

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model, $doctrine);
        $result  = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select' => ['t1.column1 as c1'],
                    'from'   => [
                        ['table' => $en, 'alias' => 't1']
                    ]
                ],
                'query_config' => [
                    'table_aliases'  => [
                        '' => 't1'
                    ],
                    'column_aliases' => [
                        'column1' => 'c1',
                    ],
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string'],
            ],
            'name'    => $gridName,
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1']
                ]
            ],
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1', 'type' => 'string']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testNoJoins()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $definition = [
            'columns' => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => '']
            ],
            'filters' => []
        ];
        $doctrine   = $this->getDoctrine(
            [
                $en => ['column1' => 'string']
            ]
        );

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model, $doctrine);
        $result  = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select' => ['t1.column1 as c1'],
                    'from'   => [
                        ['table' => $en, 'alias' => 't1']
                    ]
                ],
                'query_config' => [
                    'table_aliases'  => [
                        '' => 't1'
                    ],
                    'column_aliases' => [
                        'column1' => 'c1',
                    ],
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string'],
            ],
            'name'    => $gridName,
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1']
                ]
            ],
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1', 'type' => 'string']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testJoinFromColumns()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $en1        = 'Acme\Entity\TestEntity1';
        $definition = [
            'columns' => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => ''],
                ['name' => 'rc1,' . $en1 . '::column2', 'label' => 'lbl2', 'sorting' => ''],
            ],
            'filters' => []
        ];
        $doctrine   = $this->getDoctrine(
            [
                $en  => ['column1' => 'string'],
                $en1 => ['column2' => 'string'],
            ]
        );

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model, $doctrine);
        $result  = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select' => [
                        't1.column1 as c1',
                        't2.column2 as c2',
                    ],
                    'from'   => [
                        ['table' => $en, 'alias' => 't1']
                    ],
                    'join'   => [
                        'left' => [
                            ['join' => 't1.rc1', 'alias' => 't2'],
                        ]
                    ]
                ],
                'query_config' => [
                    'table_aliases'  => [
                        ''            => 't1',
                        $en . '::rc1' => 't2'
                    ],
                    'column_aliases' => [
                        'column1'                   => 'c1',
                        'rc1,' . $en1 . '::column2' => 'c2',
                    ],
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string'],
                'c2' => ['label' => 'lbl2', 'frontend_type' => 'string'],
            ],
            'name'    => $gridName,
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1'],
                    'c2' => ['data_name' => 't2.column2']
                ]
            ],
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1', 'type' => 'string'],
                    'c2' => ['data_name' => 't2.column2', 'type' => 'string']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testJoinFromFilters()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $en1        = 'Acme\Entity\TestEntity1';
        $definition = [
            'columns'       => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => ''],
            ],
            'filters'       => [
                [
                    'columnName' => 'rc1,' . $en1 . '::column2',
                    'criterion'  => [
                        'filter' => 'string',
                        'data'   => [
                            'type'  => '1',
                            'value' => 'test'
                        ]
                    ]
                ],
            ],
            'filters_logic' => '1'
        ];
        $doctrine   = $this->getDoctrine(
            [
                $en  => ['column1' => 'string'],
                $en1 => ['column2' => 'string'],
            ]
        );

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model, $doctrine);
        $result  = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select' => [
                        't1.column1 as c1',
                    ],
                    'from'   => [
                        ['table' => $en, 'alias' => 't1']
                    ],
                    'join'   => [
                        'left' => [
                            ['join' => 't1.rc1', 'alias' => 't2'],
                        ]
                    ]
                ],
                'query_config' => [
                    'table_aliases'  => [
                        ''            => 't1',
                        $en . '::rc1' => 't2'
                    ],
                    'column_aliases' => [
                        'column1' => 'c1',
                    ],
                    'filters'        => [
                        [
                            'column'     => 't2.column2',
                            'filter'     => 'string',
                            'filterData' => [
                                'type'  => '1',
                                'value' => 'test'
                            ]
                        ],
                    ]
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string'],
            ],
            'name'    => $gridName,
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1']
                ]
            ],
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1', 'type' => 'string']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testComplexQuery()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $en1        = 'Acme\Entity\TestEntity1';
        $en2        = 'Acme\Entity\TestEntity2';
        $en3        = 'Acme\Entity\TestEntity3';
        $definition = [
            'columns'       => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => 'DESC'],
                ['name' => 'rc1,' . $en1 . '::column2', 'label' => 'lbl2', 'sorting' => ''],
                ['name' => 'rc2,' . $en2 . '::column3', 'label' => 'lbl3', 'sorting' => 'ASC'],
            ],
            'filters'       => [
                [
                    'columnName' => 'rc1,' . $en1 . '::column2',
                    'criterion'  => [
                        'filter' => 'string',
                        'data'   => [
                            'type'  => '1',
                            'value' => 'test'
                        ]
                    ]
                ],
                [
                    'columnName' => 'rc1,' . $en1 . '::rc4,' . $en3 . '::column5',
                    'criterion'  => [
                        'filter' => 'string',
                        'data'   => [
                            'type'  => '1',
                            'value' => 'test'
                        ]
                    ]
                ],
                [
                    'columnName' => 'rc1,' . $en1 . '::rc4,' . $en3 . '::column6',
                    'criterion'  => [
                        'filter' => 'string',
                        'data'   => [
                            'type'  => '1',
                            'value' => 'test'
                        ]
                    ]
                ],
            ],
            'filters_logic' => ' 1  OR  ( 2 And ( ( 3 or 1 ) oR 2) ) '
        ];
        $doctrine   = $this->getDoctrine(
            [
                $en  => ['column1' => 'string'],
                $en1 => ['column2' => 'integer'],
                $en2 => ['column3' => 'float'],
            ]
        );

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model, $doctrine);
        $result  = $builder->getConfiguration()->toArray();

        $expected = [
            'source'  => [
                'type'         => 'orm',
                'query'        => [
                    'select' => [
                        't1.column1 as c1',
                        't2.column2 as c2',
                        't4.column3 as c3',
                    ],
                    'from'   => [
                        ['table' => $en, 'alias' => 't1']
                    ],
                    'join'   => [
                        'left' => [
                            ['join' => 't1.rc1', 'alias' => 't2'],
                            ['join' => 't2.rc4', 'alias' => 't3'],
                            ['join' => 't1.rc2', 'alias' => 't4'],
                        ]
                    ]
                ],
                'query_config' => [
                    'table_aliases'  => [
                        ''                              => 't1',
                        $en . '::rc1'                   => 't2',
                        $en . '::rc1,' . $en1 . '::rc4' => 't3',
                        $en . '::rc2'                   => 't4',
                    ],
                    'column_aliases' => [
                        'column1'                   => 'c1',
                        'rc1,' . $en1 . '::column2' => 'c2',
                        'rc2,' . $en2 . '::column3' => 'c3',
                    ],
                    'filters'        => [
                        [
                            'column'      => 't2.column2',
                            'filter'      => 'string',
                            'filterData'  => [
                                'type'  => '1',
                                'value' => 'test'
                            ],
                            'columnAlias' => 'c2'
                        ],
                        'OR',
                        [
                            [
                                'column'     => 't3.column5',
                                'filter'     => 'string',
                                'filterData' => [
                                    'type'  => '1',
                                    'value' => 'test'
                                ]
                            ],
                            'AND',
                            [
                                [
                                    [
                                        'column'     => 't3.column6',
                                        'filter'     => 'string',
                                        'filterData' => [
                                            'type'  => '1',
                                            'value' => 'test'
                                        ]
                                    ],
                                    'OR',
                                    [
                                        'column'      => 't2.column2',
                                        'filter'      => 'string',
                                        'filterData'  => [
                                            'type'  => '1',
                                            'value' => 'test'
                                        ],
                                        'columnAlias' => 'c2'
                                    ],
                                ],
                                'OR',
                                [
                                    'column'     => 't3.column5',
                                    'filter'     => 'string',
                                    'filterData' => [
                                        'type'  => '1',
                                        'value' => 'test'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1', 'frontend_type' => 'string'],
                'c2' => ['label' => 'lbl2', 'frontend_type' => 'integer'],
                'c3' => ['label' => 'lbl3', 'frontend_type' => 'decimal'],
            ],
            'sorters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1'],
                    'c2' => ['data_name' => 't2.column2'],
                    'c3' => ['data_name' => 't4.column3']
                ],
                'default' => [
                    'c1' => 'DESC',
                    'c3' => 'ASC',
                ]
            ],
            'name'    => $gridName,
            'filters' => [
                'columns' => [
                    'c1' => ['data_name' => 't1.column1', 'type' => 'string'],
                    'c2' => ['data_name' => 't2.column2', 'type' => 'number'],
                    'c3' => ['data_name' => 't4.column3', 'type' => 'number']
                ]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    private function getDoctrine(array $config = [])
    {
        $doctrine = $this->getMockBuilder('Symfony\Bridge\Doctrine\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $emMap = [];
        foreach ($config as $entity => $fields) {
            $em      = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();
            $emMap[] = [$entity, $em];

            $typeMap = [];
            foreach ($fields as $fieldName => $fieldType) {
                $typeMap[] = [$fieldName, $fieldType];
            }

            $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
            $metadata->expects($this->any())
                ->method('getTypeOfField')
                ->will($this->returnValueMap($typeMap));

            $em->expects($this->any())
                ->method('getClassMetadata')
                ->with($entity)
                ->will($this->returnValue($metadata));
        }

        if (!empty($emMap)) {
            $doctrine->expects($this->any())
                ->method('getManagerForClass')
                ->will($this->returnValueMap($emMap));
        }

        return $doctrine;
    }
}
