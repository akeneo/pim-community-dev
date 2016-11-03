<?php

namespace spec\Akeneo\ActivityManager\Bundle\Doctrine\Repository;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\ProductRepository;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\ActivityManager\Component\Repository\ProductRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\User;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilder;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Component\Security\Attributes;
use Prophecy\Argument;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactory $productQueryBuilderFactory,
        CategoryAccessRepository $categoryAccessRepository
    ) {
        $this->beConstructedWith($productQueryBuilderFactory, $categoryAccessRepository);
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
        $categoryAccessRepository,
        ProductQueryBuilder $productQueryBuilder,
        ProjectInterface $project,
        CursorInterface $products,
        UserInterface $user
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $project->getProductFilters()->willReturn([
            ['field' => 'family.code', 'operator' => 'IN', 'value' => 'guitar'],
            ['field' => 'name', 'operator' => '=', 'value' => 'Gibson Les Paul']
        ]);

        $productQueryBuilder->addFilter('family.code', 'IN', 'guitar')->shouldBeCalled();
        $productQueryBuilder->addFilter('name', '=', 'Gibson Les Paul')->shouldBeCalled();

        $project->getOwner()->willReturn($user);
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)->willReturn([42, 65]);
        $productQueryBuilder->addFilter('categories.id', 'IN', [42, 65])->shouldBeCalled();
        $productQueryBuilder->addFilter('family.id', 'NOT EMPTY', null)->shouldBeCalled();

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
