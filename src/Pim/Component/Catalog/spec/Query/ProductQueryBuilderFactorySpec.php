<?php

namespace spec\Pim\Component\Catalog\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductQueryBuilderFactorySpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attRepository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        ObjectManager $om
    ) {
        $this->beConstructedWith(
            'Pim\Component\Catalog\Query\ProductQueryBuilder',
            $om,
            'Pim\Component\Catalog\Model\Product',
            $attRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory
        );
    }

    function it_is_a_product_query_factory()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface');
    }

    function it_creates_a_product_query_builder_with_the_default_repository_method($om, ProductRepository $repository)
    {
        $om->getRepository(Argument::any())->willReturn($repository);
        $repository->createQueryBuilder('o')->shouldBeCalled();

        $this->create(['default_locale' => 'en_US', 'default_scope' => 'print']);
    }

    function it_creates_a_product_query_builder_with_a_custom_repository_method($om, ProductRepository $repository)
    {
        $om->getRepository(Argument::any())->willReturn($repository);
        $repository->createDatagridQueryBuilder(['param1'])->shouldBeCalled();

        $this->create(
            [
                'default_locale' => 'en_US',
                'default_scope' => 'print',
                'repository_method' => 'createDatagridQueryBuilder',
                'repository_parameters' => ['param1']
            ]
        );
    }
}
