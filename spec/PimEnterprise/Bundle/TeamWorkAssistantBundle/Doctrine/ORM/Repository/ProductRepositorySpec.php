<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository;

use PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository\ProductRepository;
use PimEnterprise\Component\TeamWorkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProductRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactory;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;

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
        ProductQueryBuilderInterface $productQueryBuilder,
        ProjectInterface $project,
        CursorInterface $products,
        UserInterface $user,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $project->getLocale()->willReturn($locale);
        $project->getChannel()->willReturn($channel);

        $locale->getCode()->willReturn('en_US');
        $channel->getCode()->willReturn('ecommerce');

        $productQueryBuilderFactory->create([
            'default_locale' => 'en_US',
            'default_scope' => 'ecommerce',
        ])->willReturn($productQueryBuilder);

        $project->getProductFilters()->willReturn([
            ['field' => 'family.code', 'operator' => 'IN', 'value' => 'guitar'],
            ['field' => 'name', 'operator' => '=', 'value' => 'Gibson Les Paul'],
        ]);

        $productQueryBuilder->addFilter('family.code', 'IN', 'guitar')->shouldBeCalled();
        $productQueryBuilder->addFilter('name', '=', 'Gibson Les Paul')->shouldBeCalled();

        $project->getOwner()->willReturn($user);
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::VIEW_ITEMS)->willReturn([42, 65]);
        $productQueryBuilder->addFilter('categories.id', 'IN OR UNCLASSIFIED', [42, 65])->shouldBeCalled();
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
