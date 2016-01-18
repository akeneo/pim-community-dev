<?php

namespace spec\Pim\Bundle\CatalogBundle\Query\Filter;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Output\OutputInterface;

class AttributeFilterDumperSpec extends ObjectBehavior
{
    function let(FilterRegistryInterface $registry, AttributeRepositoryInterface $repository)
    {
        $this->beConstructedWith($registry, $repository);
    }

    function it_is_a_dumper()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\DumperInterface');
    }

    function it_dumps_field_filters(OutputInterface $output, HelperSet $helperSet, TableHelper $table, $repository)
    {
        $output->writeln(Argument::any())->shouldBeCalled();
        $repository->findAll()->willReturn([]);
        $helperSet->get('table')->willReturn($table);
        $headers = ['attribute', 'localizable', 'scopable', 'attribute type', 'filter_class', 'operators'];
        $table->setHeaders($headers)->shouldBeCalled()->willReturn($table);
        $table->setRows(Argument::any())->shouldBeCalled();
        $table->render(Argument::any())->shouldBeCalled();

        $this->dump($output, $helperSet);
    }
}
