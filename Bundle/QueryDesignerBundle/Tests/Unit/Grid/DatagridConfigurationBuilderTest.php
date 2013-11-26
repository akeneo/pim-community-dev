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
        new DatagridConfigurationBuilder('test_grid', $model);
    }

    /**
     * @expectedException \Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The "columns" definition must not be empty.
     */
    public function testEmptyColumns()
    {
        $model = new QueryDesignerModel();
        $model->setDefinition(json_encode(['columns' => []]));
        new DatagridConfigurationBuilder('test_grid', $model);
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

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model);
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
                'c1' => ['label' => 'lbl1'],
            ],
            'name'    => $gridName
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

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model);
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
                'c1' => ['label' => 'lbl1'],
            ],
            'name'    => $gridName
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

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model);
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
                'c1' => ['label' => 'lbl1'],
                'c2' => ['label' => 'lbl2'],
            ],
            'name'    => $gridName
        ];

        $this->assertEquals($expected, $result);
    }

    public function testJoinFromFilters()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $en1        = 'Acme\Entity\TestEntity1';
        $definition = [
            'columns' => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => ''],
            ],
            'filters' => [
                ['columnName' => 'rc1,' . $en1 . '::column2', 'criterion' => []],
            ]
        ];

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model);
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
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1'],
            ],
            'name'    => $gridName
        ];

        $this->assertEquals($expected, $result);
    }

    public function testComplexQuery()
    {
        $gridName   = 'test_grid';
        $en         = 'Acme\Entity\TestEntity';
        $en1        = 'Acme\Entity\TestEntity1';
        $en2        = 'Acme\Entity\TestEntity2';
        $en3        = 'Acme\Entity\TestEntity3';
        $definition = [
            'columns' => [
                ['name' => 'column1', 'label' => 'lbl1', 'sorting' => 'DESC'],
                ['name' => 'rc1,' . $en1 . '::column2', 'label' => 'lbl2', 'sorting' => ''],
                ['name' => 'rc2,' . $en2 . '::column3', 'label' => 'lbl3', 'sorting' => 'ASC'],
            ],
            'filters' => [
                ['columnName' => 'rc1,' . $en1 . '::c2', 'criterion' => []],
                [
                    'columnName' => 'rc1,' . $en1 . '::rc4,' . $en3 . '::c5',
                    'criterion'  => []
                ],
            ]
        ];

        $model = new QueryDesignerModel();
        $model->setEntity($en);
        $model->setDefinition(json_encode($definition));
        $builder = new DatagridConfigurationBuilder($gridName, $model);
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
                ],
            ],
            'columns' => [
                'c1' => ['label' => 'lbl1'],
                'c2' => ['label' => 'lbl2'],
                'c3' => ['label' => 'lbl3'],
            ],
            'sorters' => [
                'default' => [
                    'c1' => 'DESC',
                    'c3' => 'ASC',
                ]
            ],
            'name'    => $gridName
        ];

        $this->assertEquals($expected, $result);
    }
}
