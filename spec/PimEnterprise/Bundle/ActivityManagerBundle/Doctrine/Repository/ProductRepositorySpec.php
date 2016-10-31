<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\ProductRepository;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProductRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use Prophecy\Argument;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(ProductQueryBuilderFactory $productQueryBuilderFactory)
    {
        $this->beConstructedWith($productQueryBuilderFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRepository::class);
    }

    function it_is_product_repository()
    {
        $this->shouldImplement(ProductRepositoryInterface::class);
    }

    function it_finds_the_product_affected_by_the_project(
        $productQueryBuilderFactory,
        ProductQueryBuilder $productQueryBuilder,
        ProjectInterface $project,
        CursorInterface $products
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $project->getProductFilters()->willReturn([
            ['field' => 'family.code', 'operator' => 'IN', 'value' => 'guitar'],
            ['field' => 'name', 'operator' => '=', 'value' => 'Gibson Les Paul']
        ]);

        $productQueryBuilder->addFilter('family.code', 'IN', 'guitar')->shouldBeCalled();
        $productQueryBuilder->addFilter('name', '=', 'Gibson Les Paul')->shouldBeCalled();

        $productQueryBuilder->execute()->willReturn($products);

        $this->findByProject($project)->shouldReturn($products);
    }

    function it_throws_an_exception_a_project_does_not_have_product_filter(ProjectInterface $project)
    {
        $project->getProductFilters()->willReturn(null);
        $project->getLabel()->shouldBeCalled();

        $this->shouldThrow(\LogicException::class)->during('findByProject', [$project]);
    }
}
