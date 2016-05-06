<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\DataGrid\Extension\Selector\ORM;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\DataGridBundle\Datasource\Datasource;
use Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface;

class ReferenceDataSelectorSpec extends ObjectBehavior
{
    function let(SelectorInterface $predecessor) {
        $this->beConstructedWith($predecessor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ReferenceDataBundle\DataGrid\Extension\Selector\ORM\ReferenceDataSelector');
        $this->shouldImplement('Pim\Bundle\DataGridBundle\Extension\Selector\SelectorInterface');
    }

    function it_applies_the_reference_data_selector_to_the_data_source(
        $predecessor,
        Datasource $dataSource,
        DatagridConfiguration $configuration,
        QueryBuilder $qb,
        Join $join
    ) {
        $source = [
            'attributes_configuration' => [
                'sku' => [
                    'properties' => [
                        'reference_data_name' => ''
                    ],
                ],
                'color' => [
                    'properties' => [
                        'reference_data_name' => 'sole_color'
                    ],
                ],
            ],
        ];
        $columns = [
            'sku' => [
                'some_config',
                'some_other_config'
            ],
            'color' => [
                'some_config',
                'some_other_config'
            ],
        ];

        $predecessor->apply($dataSource, $configuration)->shouldBeCalled();

        $configuration->offsetGet('source')->willReturn($source);
        $configuration->offsetGet('columns')->willReturn($columns);

        $dataSource->getQueryBuilder()->willReturn($qb);

        $qb->leftJoin('values.', '')->shouldNotBeCalled();
        $qb->leftJoin('values.sole_color', 'sole_color')->shouldBeCalled()->willReturn($qb);
        $qb->addSelect('sole_color')->shouldBeCalled()->willReturn($qb);
        $qb->getDQLPart('join')->willReturn(['p' => [$join]]);
        $qb->getRootAliases()->willReturn(['p']);

        $join->getAlias()->willReturn('values');

        $this->apply($dataSource, $configuration);
    }
}
