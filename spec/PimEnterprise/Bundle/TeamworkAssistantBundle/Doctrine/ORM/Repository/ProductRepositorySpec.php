<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\User\Model\UserInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Doctrine\ORM\Repository\ProductRepository;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProductRepositoryInterface;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
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
        $categoryAccessRepository->getGrantedCategoryCodes($user, Attributes::VIEW_ITEMS)->willReturn(['foo', 'bar']);
        $productQueryBuilder->addFilter('categories', 'IN OR UNCLASSIFIED', ['foo', 'bar'])->shouldBeCalled();
        $productQueryBuilder->addFilter('family', 'NOT EMPTY', null)->shouldBeCalled();

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
