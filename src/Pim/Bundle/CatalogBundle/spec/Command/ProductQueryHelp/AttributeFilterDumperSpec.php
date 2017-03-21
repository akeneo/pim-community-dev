<?php

namespace spec\Pim\Bundle\CatalogBundle\Command\ProductQueryHelp;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
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
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Command\DumperInterface');
    }

    function it_dumps_field_filters(
        $repository,
        $registry,
        OutputInterface $output,
        HelperSet $helperSet,
        TableHelper $table,
        AttributeFilterInterface $mediaFilter,
        AttributeInterface $pictureAttribute
    ) {
        $output->writeln(Argument::any())->shouldBeCalled();
        $repository->findAll()->willReturn([$pictureAttribute]);

        $registry->getAttributeFilters()->willReturn([$mediaFilter]);
        $mediaFilter->getAttributeTypes()->willReturn([AttributeTypes::IMAGE, AttributeTypes::FILE]);
        $operators = ['STARTS WITH', 'ENDS WITH'];
        $mediaFilter->getOperators()->willReturn($operators);

        $pictureAttribute->getCode()->willReturn('picture');
        $pictureAttribute->getType()->willReturn(AttributeTypes::IMAGE);
        $pictureAttribute->isScopable()->willReturn(false);
        $pictureAttribute->isLocalizable()->willReturn(false);

        $helperSet->get('table')->willReturn($table);
        $headers = ['attribute', 'localizable', 'scopable', 'attribute type', 'operators', 'filter_class'];
        $table->setHeaders($headers)->shouldBeCalled()->willReturn($table);
        $table->setRows(Argument::that(function ($param) {
            return 'picture' === $param[0][0] &&
                'no' === $param[0][1] &&
                'no' === $param[0][2] &&
                AttributeTypes::IMAGE === $param[0][3] &&
                'STARTS WITH, ENDS WITH' === $param[0][4] &&
                false !== strpos($param[0][5], 'AttributeFilterInterface');
        }))->shouldBeCalled();
        $table->render(Argument::any())->shouldBeCalled();

        $this->dump($output, $helperSet);
    }

    function it_dumps_reference_data_filter(
        $repository,
        $registry,
        OutputInterface $output,
        HelperSet $helperSet,
        TableHelper $table,
        AttributeFilterInterface $refDataFilter,
        AttributeInterface $refDataAttribute
    ) {
        $output->writeln(Argument::any())->shouldBeCalled();
        $repository->findAll()->willReturn([$refDataAttribute]);

        $registry->getAttributeFilters()->willReturn([$refDataFilter]);
        $refDataFilter->getAttributeTypes()->willReturn([]);
        $operators = ['IN', 'EMPTY'];
        $refDataFilter->getOperators()->willReturn($operators);
        $refDataFilter->supportsAttribute($refDataAttribute)->willReturn(true);

        $refDataAttribute->getCode()->willReturn('ref_data');
        $refDataAttribute->getType()->willReturn(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);
        $refDataAttribute->isScopable()->willReturn(false);
        $refDataAttribute->isLocalizable()->willReturn(false);
        $refDataAttribute->isBackendTypeReferenceData()->willReturn(true);

        $helperSet->get('table')->willReturn($table);
        $headers = ['attribute', 'localizable', 'scopable', 'attribute type', 'operators', 'filter_class'];
        $table->setHeaders($headers)->shouldBeCalled()->willReturn($table);
        $table->setRows(Argument::that(function ($param) {
            return 'ref_data' === $param[0][0] &&
            'no' === $param[0][1] &&
            'no' === $param[0][2] &&
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT === $param[0][3] &&
            'IN, EMPTY' === $param[0][4] &&
            false !== strpos($param[0][5], 'AttributeFilterInterface');
        }))->shouldBeCalled();
        $table->render(Argument::any())->shouldBeCalled();

        $this->dump($output, $helperSet);
    }
}
