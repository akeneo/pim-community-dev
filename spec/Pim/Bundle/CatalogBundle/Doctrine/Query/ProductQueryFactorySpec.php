<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Query;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QueryFilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\QuerySorterRegistryInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProductQueryFactorySpec extends ObjectBehavior
{
    function let(
        AttributeRepository $attRepository,
        QueryFilterRegistryInterface $filterRegistry,
        QuerySorterRegistryInterface $sorterRegistry,
        ObjectManager $om
    ) {
        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryBuilder',
            $om,
            'Pim\Bundle\CatalogBundle\Model\Product',
            $attRepository,
            $filterRegistry,
            $sorterRegistry
        );
    }

    function it_is_a_product_query_factory()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactoryInterface');
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
