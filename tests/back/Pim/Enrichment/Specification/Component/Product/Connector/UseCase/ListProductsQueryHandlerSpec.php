<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListProductsQueryHandlerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        GetConnectorProducts $getConnectorProducts
    ) {
        $this->beConstructedWith(
            $channelRepository,
            new ApplyProductSearchQueryParametersToPQB($channelRepository->getWrappedObject()),
            $fromSizePqbFactory,
            $searchAfterPqbFactory,
            $primaryKeyEncrypter,
            $getConnectorProducts
        );
    }

    function it_gets_connector_products_with_offset_method(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::OFFSET;
        $query->limit = 42;
        $query->page = 69;
        $query->channelCode = 'tablet';
        $query->localeCodes = ['en_US'];
        $query->attributeCodes = ['name'];
        $query->userId = 1;

        $fromSizePqbFactory->create([
            'limit' => 42,
            'from' => 2856
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();

        $connectorProduct1 = new ConnectorProduct(
            1,
            'identifier_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            new ReadValueCollection()
        );

        $connectorProduct2 = new ConnectorProduct(
            2,
            'identifier_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_3', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            new ReadValueCollection()
        );

        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, ['name'], 'tablet', ['en_US'])
            ->willReturn(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]));

        $searchAfterPqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]));
    }

    function it_gets_connector_products_with_search_after_method(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = '69';
        $query->userId = 1;

        $primaryKeyEncrypter->decrypt('69')->shouldBeCalled()->willReturn('encoded69');

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'product_encoded69',
            'search_after' => ['product_encoded69']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();

        $connectorProduct1 = new ConnectorProduct(
            1,
            'identifier_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            new ReadValueCollection()
        );

        $connectorProduct2 = new ConnectorProduct(
            2,
            'identifier_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_3', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            new ReadValueCollection()
        );

        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, null, null)
            ->willReturn(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]));
    }

    function it_filters_with_activated_locales_of_the_provided_channel_filter_of_the_query_when_no_locales_filter_provided(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts,
        IdentifiableObjectRepositoryInterface $channelRepository,
        ChannelInterface $channel,
        Category $category
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->channelCode = 'tablet';
        $query->userId = 1;

        $channel->getLocaleCodes()->willReturn(['en_US']);
        $channel->getCategory()->willReturn($category);
        $category->getCode()->willReturn('master');
        $channelRepository->findOneByIdentifier('tablet')->willReturn($channel);

        $searchAfterPqbFactory->create([
            'limit' => 42
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('categories', 'IN CHILDREN', ['master'], ['locale' => null, 'scope' => null])->shouldBeCalled();

        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, 'tablet', ['en_US'])
            ->willReturn(new ConnectorProductList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(0, []));
    }

    function it_filters_with_provided_locales_filter_of_the_query_when_no_channel_filter_is_provided(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = null;
        $query->channelCode = null;
        $query->localeCodes = ['en_US', 'fr_FR'];
        $query->userId = 1;

        $searchAfterPqbFactory->create([
            'limit' => 42
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();

        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, null, ['en_US', 'fr_FR'])
            ->willReturn(new ConnectorProductList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(0, []));
    }

    function it_filters_with_provided_locales_filter_of_the_query_when_channel_filter_is_provided_as_locales_are_already_validated_as_activated_for_this_channel(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = null;
        $query->channelCode = 'tablet';
        $query->localeCodes = ['en_US', 'fr_FR'];
        $query->userId = 1;

        $searchAfterPqbFactory->create([
            'limit' => 42
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();


        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, 'tablet', ['en_US', 'fr_FR'])
            ->willReturn(new ConnectorProductList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(0, []));
    }
}
