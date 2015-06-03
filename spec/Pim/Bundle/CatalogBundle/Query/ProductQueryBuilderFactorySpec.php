<?php

namespace spec\Pim\Bundle\CatalogBundle\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ProductRepository;
use Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Query\Sorter\SorterRegistryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
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
            'Pim\Bundle\CatalogBundle\Query\ProductQueryBuilder',
            $om,
            'Pim\Bundle\CatalogBundle\Model\Product',
            $attRepository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory
        );
    }

    function it_is_a_product_query_factory()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface');
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
