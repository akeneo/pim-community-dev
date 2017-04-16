<?php

namespace spec\Pim\Bundle\CatalogBundle\Command\ProductQueryHelp;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\OutputInterface;

class FieldFilterDumperSpec extends ObjectBehavior
{
    function let(FilterRegistryInterface $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_a_dumper()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Command\DumperInterface');
    }

    function it_dumps_field_filters(
        $registry,
        OutputInterface $output,
        HelperSet $helperSet,
        TableHelper $table,
        FieldFilterInterface $groupFilter
    ) {
        $output->writeln(Argument::any())->shouldBeCalled();

        $operators = ['IN', 'NOT IN', 'EMPTY', 'NOT EMPTY'];
        $fields = ['groups', 'groups'];
        $registry->getFieldFilters()->willReturn([$groupFilter]);
        $groupFilter->getOperators()->willReturn($operators);
        $groupFilter->getFields()->willReturn($fields);

        $helperSet->get('table')->willReturn($table);
        $headers = ['field', 'operators', 'filter_class'];
        $table->setHeaders($headers)->shouldBeCalled()->willReturn($table);
        $table->setRows(Argument::that(function ($param) {
            return 'groups' === $param[0][0] &&
                'IN, NOT IN, EMPTY, NOT EMPTY' === $param[0][1] &&
                false !== strpos($param[0][2], 'FieldFilterInterface') &&
                'groups' === $param[1][0] &&
                'IN, NOT IN, EMPTY, NOT EMPTY' === $param[1][1] &&
                false !== strpos($param[1][2], 'FieldFilterInterface');
        }))->shouldBeCalled();
        $table->render(Argument::any())->shouldBeCalled();

        $this->dump($output, $helperSet);
    }
}
