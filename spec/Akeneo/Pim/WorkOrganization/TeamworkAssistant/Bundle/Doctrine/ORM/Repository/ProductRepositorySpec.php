<?php

namespace spec\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;

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
