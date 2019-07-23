<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

final class ListProductModelsQueryHandlerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        GetConnectorProductModels $getConnectorProductModels
    ) {
        $this->beConstructedWith(
            new ApplyProductSearchQueryParametersToPQB($channelRepository->getWrappedObject()),
            $fromSizePqbFactory,
            $searchAfterPqbFactory,
            $primaryKeyEncrypter,
            $getConnectorProductModels,
            $channelRepository
        );
    }

    function it_gets_connector_product_models_with_offset_method(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $productQueryBuilder,
        GetConnectorProductModels $getConnectorProductModels
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::OFFSET;
        $query->limit = 42;
        $query->page = 69;
        $query->channelCode = 'tablet';
        $query->localeCodes = ['en_US'];
        $query->attributeCodes = ['name'];
        $query->userId = 42;

        $fromSizePqbFactory->create([
            'limit' => 42,
            'from' => 2856
        ])->shouldBeCalled()->willReturn($productQueryBuilder);

        $productQueryBuilder->addSorter('id', Directions::ASCENDING)->shouldBeCalled();

        $connectorProductModel1 = new ConnectorProductModel(
            1234,
            'code_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            'my_parent',
            'my_family',
            'my_family_variant',
            ['workflow_status' => 'working_copy'],
            [],
            ['category_code_1'],
            new ReadValueCollection()
        );

        $connectorProductModel2 = new ConnectorProductModel(
            5678,
            'code_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            'my_parent',
            'my_family',
            'my_family_variant',
            ['workflow_status' => 'in_progress'],
            [],
            ['category_code_4'],
            new ReadValueCollection()
        );


        $getConnectorProductModels
            ->fromProductQueryBuilder($productQueryBuilder, 42, ['name'], 'tablet', ['en_US'])
            ->willReturn(new ConnectorProductModelList(2, [$connectorProductModel1, $connectorProductModel2]));

        $searchAfterPqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductModelList(2, [$connectorProductModel1, $connectorProductModel2]));
    }

    function it_gets_connector_product_models_with_search_after_method(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $productQueryBuilder,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        GetConnectorProductModels $getConnectorProductModels
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = '69';
        $query->userId = 42;

        $primaryKeyEncrypter->decrypt('69')->shouldBeCalled()->willReturn('encoded69');

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'product_model_encoded69',
            'search_after' => ['product_model_encoded69']
        ])->shouldBeCalled()->willReturn($productQueryBuilder);

        $productQueryBuilder->addSorter('id', Directions::ASCENDING)->shouldBeCalled();

        $connectorProductModel1 = new ConnectorProductModel(
            1234,
            'code_1',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            'my_parent',
            'my_family',
            'my_family_variant',
            ['workflow_status' => 'working_copy'],
            [],
            ['category_code_1'],
            new ReadValueCollection()
        );

        $connectorProductModel2 = new ConnectorProductModel(
            5678,
            'code_2',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            'my_parent',
            'my_family',
            'my_family_variant',
            ['workflow_status' => 'in_progress'],
            [],
            ['category_code_4'],
            new ReadValueCollection()
        );


        $getConnectorProductModels
            ->fromProductQueryBuilder($productQueryBuilder, 42, null, null, null)
            ->willReturn(new ConnectorProductModelList(2, [$connectorProductModel1, $connectorProductModel2]));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductModelList(2, [$connectorProductModel1, $connectorProductModel2]));
    }

    function it_filters_with_activated_locales_of_the_provided_channel_filter_of_the_query_when_no_locales_filter_provided(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProductModels $getConnectorProductModels,
        IdentifiableObjectRepositoryInterface $channelRepository,
        ChannelInterface $channel,
        Category $category
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->channelCode = 'tablet';
        $query->userId = 42;

        $channel->getLocaleCodes()->willReturn(['en_US']);
        $channel->getCategory()->willReturn($category);
        $category->getCode()->willReturn('master');
        $channelRepository->findOneByIdentifier('tablet')->willReturn($channel);

        $searchAfterPqbFactory->create([
            'limit' => 42
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('categories', 'IN CHILDREN', ['master'], ['locale' => null, 'scope' => null])->shouldBeCalled();

        $getConnectorProductModels
            ->fromProductQueryBuilder($pqb, 42, null, 'tablet', ['en_US'])
            ->willReturn(new ConnectorProductModelList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductModelList(0, []));
    }

    function it_filters_with_provided_locales_filter_of_the_query_when_no_channel_filter_is_provided(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProductModels $getConnectorProductModels
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = null;
        $query->channelCode = null;
        $query->localeCodes = ['en_US', 'fr_FR'];
        $query->userId = 42;

        $searchAfterPqbFactory->create([
            'limit' => 42
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();

        $getConnectorProductModels
            ->fromProductQueryBuilder($pqb, 42, null, null, ['en_US', 'fr_FR'])
            ->willReturn(new ConnectorProductModelList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductModelList(0, []));
    }

    function it_filters_with_provided_locales_filter_of_the_query_when_channel_filter_is_provided_as_locales_are_already_validated_as_activated_for_this_channel(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProductModels $getConnectorProductModels
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = null;
        $query->channelCode = 'tablet';
        $query->localeCodes = ['en_US', 'fr_FR'];
        $query->userId = 42;

        $searchAfterPqbFactory->create([
            'limit' => 42
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();


        $getConnectorProductModels
            ->fromProductQueryBuilder($pqb, 42, null, 'tablet', ['en_US', 'fr_FR'])
            ->willReturn(new ConnectorProductModelList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductModelList(0, []));
    }
}
