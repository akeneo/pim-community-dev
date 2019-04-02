<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApplyProductSearchQueryParametersToPQBSpec extends ObjectBehavior
{

    function let(IdentifiableObjectRepositoryInterface $channelRepository)
    {
        $this->beConstructedWith($channelRepository);
    }

    function it_adds_no_filter(ProductQueryBuilderInterface $pqb)
    {
        $pqb->addFilter(Argument::cetera())->shouldNotBeCalled();

        $this->apply($pqb, new ListProductsQuery());
    }

    function it_adds_search_filter(ProductQueryBuilderInterface $pqb)
    {
        $query = new ListProductsQuery();
        $query->search = [
            'propertyCode' => [
                [
                    'operator' => 'op',
                    'value' => 'val',
                ],
            ],
        ];
        $query->searchLocale = 'en_US';
        $query->searchScope = 'ecommerce';

        $pqb->addFilter('propertyCode', 'op', 'val', ['locale' => 'en_US', 'scope' => 'ecommerce'])->shouldBeCalled();

        $this->apply($pqb, $query);
    }

    function it_adds_default_category_from_scope(
        ProductQueryBuilderInterface $pqb,
        IdentifiableObjectRepositoryInterface $channelRepository,
        ChannelInterface $channel,
        CategoryInterface $category
    ) {
        $query = new ListProductsQuery();
        $query->searchLocale = 'en_US';
        $query->searchScope = 'ecommerce';
        $query->channel = 'ecommerce';

        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($channel)->shouldBeCalled();
        $channel->getCategory()->willReturn($category)->shouldBeCalled();
        $category->getCode()->willReturn('categoryCode')->shouldBeCalled();

        $pqb->addFilter(
            'categories',
            Operators::IN_CHILDREN_LIST,
            ['categoryCode'],
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        )->shouldBeCalled();

        $this->apply($pqb, $query);
    }

    function it_adds_search_filter_specifying_scope_and_locale(ProductQueryBuilderInterface $pqb)
    {
        $query = new ListProductsQuery();
        $query->search = [
            'propertyCode' => [
                [
                    'operator' => 'op',
                    'value' => 'val',
                    'scope' => 'mobile',
                    'locale' => 'fr_FR',
                ],
            ],
        ];
        $query->searchLocale = 'en_US';

        $pqb->addFilter('propertyCode', 'op', 'val', ['locale' => 'fr_FR', 'scope' => 'mobile'])->shouldBeCalled();

        $this->apply($pqb, $query);
    }

    function it_adds_search_filter_for_datetimes(ProductQueryBuilderInterface $pqb)
    {
        $query = new ListProductsQuery();
        $query->search = [
            'created' => [
                [
                    'operator' => Operators::BETWEEN,
                    'value' => ['2019-01-28 12:12:12', '2019-02-28 13:13:13'],
                ],
            ],
            'updated' => [
                [
                    'operator' => Operators::LOWER_THAN,
                    'value' => '2020-03-38 14:14:14',
                ],
            ],
        ];
        $query->searchLocale = 'en_US';
        $query->searchScope = 'ecommerce';

        $pqb->addFilter('created', Operators::BETWEEN, Argument::any(), ['locale' => 'en_US', 'scope' => 'ecommerce'])
            ->shouldBeCalled();
        $pqb->addFilter(
            'updated',
            Operators::LOWER_THAN,
            Argument::type(\DateTime::class),
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        )->shouldBeCalled();

        $this->apply($pqb, $query);
    }
}
