<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;

class AttributeFilterDumperSpec extends ObjectBehavior
{
    function let(QueryFilterRegistryInterface $registry, AttributeRepository $repository)
    {
        $this->beConstructedWith($registry, $repository);
    }

    function it_is_a_dumper()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\DumperInterface');
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
