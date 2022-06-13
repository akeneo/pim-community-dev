<?php

namespace Specification\Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetGrantedCategoryCodes;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Doctrine\ORM\Repository\ProductRepository;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;

class ProductRepositorySpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        GetGrantedCategoryCodes $getAllGrantedCategoryCodes
    ) {
        $this->beConstructedWith($productQueryBuilderFactory, $getAllGrantedCategoryCodes);
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
        GetGrantedCategoryCodes $getAllGrantedCategoryCodes,
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
            ['field' => 'completeness', 'operator' => 'AT LEAST INCOMPLETE', 'value' => null],
        ]);

        $productQueryBuilder->addFilter('family.code', 'IN', 'guitar')->shouldBeCalled();
        $productQueryBuilder->addFilter('name', '=', 'Gibson Les Paul')->shouldBeCalled();
        $productQueryBuilder->addFilter('completeness', '<', 100)->shouldBeCalled();

        $project->getOwner()->willReturn($user);
        $user->getGroupsIds()->willReturn([1,2]);
        $getAllGrantedCategoryCodes->forGroupIds([1,2])->willReturn(['foo', 'bar']);
        $productQueryBuilder->addFilter('categories', 'IN OR UNCLASSIFIED', ['foo', 'bar'], ['type_checking' => false])->shouldBeCalled();
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

    public function it_converts_filters_on_completeness(
        $productQueryBuilderFactory,
        GetGrantedCategoryCodes $getAllGrantedCategoryCodes,
        ProductQueryBuilderInterface $productQueryBuilder,
        ProjectInterface $project,
        CursorInterface $products,
        UserInterface $user,
        ChannelInterface $channel,
        LocaleInterface $locale
    ): void
    {
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
            ['field' => 'completeness', 'operator' => 'AT LEAST COMPLETE', 'value' => null],
        ]);

        $productQueryBuilder->addFilter('family.code', 'IN', 'guitar')->shouldBeCalled();
        $productQueryBuilder->addFilter('completeness', '=', 100)->shouldBeCalled();

        $project->getOwner()->willReturn($user);
        $user->getGroupsIds()->willReturn([1,2]);
        $getAllGrantedCategoryCodes->forGroupIds([1,2])->willReturn(['foo', 'bar']);
        $productQueryBuilder->addFilter('categories', 'IN OR UNCLASSIFIED', ['foo', 'bar'], ['type_checking' => false])->shouldBeCalled();
        $productQueryBuilder->addFilter('family', 'NOT EMPTY', null)->shouldBeCalled();

        $productQueryBuilder->execute()->willReturn($products);

        $this->findByProject($project)->shouldReturn($products);
    }
}
