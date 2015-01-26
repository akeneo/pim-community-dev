<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;

class FieldFilterDumperSpec extends ObjectBehavior
{
    function let(QueryFilterRegistryInterface $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_a_dumper()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\DumperInterface');
    }

    function it_dumps_field_filters(OutputInterface $output, HelperSet $helperSet, TableHelper $table)
    {
        $output->writeln(Argument::any())->shouldBeCalled();
        $helperSet->get('table')->willReturn($table);
        $headers = ['field', 'filter_class', 'operators'];
        $table->setHeaders($headers)->shouldBeCalled()->willReturn($table);
        $table->setRows(Argument::any())->shouldBeCalled();
        $table->render(Argument::any())->shouldBeCalled();

        $this->dump($output, $helperSet);
    }
}
