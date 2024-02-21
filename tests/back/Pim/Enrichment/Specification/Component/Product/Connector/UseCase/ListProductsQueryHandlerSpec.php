<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Category\Infrastructure\Component\Model\Category;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Event\Connector\ReadProductsEvent;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ListProductsQueryHandlerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        GetConnectorProducts $getConnectorProducts,
        GetConnectorProducts $getConnectorProductsWithOptions,
        EventDispatcherInterface $eventDispatcher,
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        FindId $findProductId
    ) {
        $eventDispatcher->dispatch(Argument::any())->willReturn(Argument::type('object'));
        $this->beConstructedWith(
            $channelRepository,
            new ApplyProductSearchQueryParametersToPQB($channelRepository->getWrappedObject()),
            $fromSizePqbFactory,
            $searchAfterPqbFactory,
            $getConnectorProducts,
            $getConnectorProductsWithOptions,
            $eventDispatcher,
            $getProductsWithQualityScores,
            $getProductsWithCompletenesses,
            $findProductId
        );
    }

    function it_gets_connector_products_with_attribute_options(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProductsWithOptions
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::OFFSET;
        $query->limit = 42;
        $query->page = 69;
        $query->channelCode = 'tablet';
        $query->localeCodes = ['en_US'];
        $query->attributeCodes = ['name'];
        $query->userId = 1;
        $query->withAttributeOptions = 'true';

        $fromSizePqbFactory->create([
            'limit' => 42,
            'from' => 2856
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $connectorProduct1 = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
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
            [],
            new ReadValueCollection(),
            null,
            null
        );

        $connectorProduct2 = new ConnectorProduct(
            Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'),
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
            [],
            new ReadValueCollection(),
            null,
            null
        );

        $getConnectorProductsWithOptions
            ->fromProductQueryBuilder($pqb, 1, ['name'], 'tablet', ['en_US'])
            ->willReturn(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]));

        $searchAfterPqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]));
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

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $connectorProduct1 = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
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
            [],
            new ReadValueCollection(),
            null,
            null
        );

        $connectorProduct2 = new ConnectorProduct(
            Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'),
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
            [],
            new ReadValueCollection(),
            null,
            null
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
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = 'AN-UPPERCASE-IDENTIFIER';
        $query->userId = 1;

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'product_z',
            'search_after' => ['an-uppercase-identifier']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $connectorProduct1 = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
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
            [],
            new ReadValueCollection(),
            null,
            null
        );

        $connectorProduct2 = new ConnectorProduct(
            Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'),
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
            [],
            new ReadValueCollection(),
            null,
            null
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

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('categories', 'IN CHILDREN', ['master'], ['locale' => null, 'scope' => null])->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

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

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

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

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, 'tablet', ['en_US', 'fr_FR'])
            ->willReturn(new ConnectorProductList(0, []));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(0, []));
    }

    function it_dispatches_a_read_products_event_when_querying_products(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts,
        EventDispatcherInterface $eventDispatcher
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = '69';
        $query->userId = 1;

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'product_z',
            'search_after' => ['69']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $connectorProduct = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            'identifier_5',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            [],
            new ReadValueCollection(),
            null,
            null
        );

        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, null, null)
            ->willReturn(new ConnectorProductList(1, [$connectorProduct]));

        $eventDispatcher->dispatch(new ReadProductsEvent(1))->shouldBeCalled();

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(1, [$connectorProduct]));
    }

    function it_add_quality_scores_to_products_if_option_is_activated(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts,
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = '69';
        $query->userId = 1;
        $query->withQualityScores = 'true';

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'product_z',
            'search_after' => ['69']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $connectorProduct = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            'identifier_5',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            [],
            new ReadValueCollection(),
            null,
            null
        );
        $connectorProductWithQualityScores = $connectorProduct->buildWithQualityScores(
            new QualityScoreCollection(['ecommerce' => ['en_US' => new QualityScore('E', 15)]])
        );

        $productList = new ConnectorProductList(1, [$connectorProduct]);
        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, null, null)
            ->willReturn($productList);

        $getProductsWithQualityScores->fromConnectorProductList($productList, null, [])->willReturn(new ConnectorProductList(1, [$connectorProductWithQualityScores]));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(1, [$connectorProductWithQualityScores]));
    }

    function it_adds_completenesses_to_products_if_option_is_activated(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        GetConnectorProducts $getConnectorProducts,
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses
    ) {
        $query = new ListProductsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = '69';
        $query->userId = 1;
        $query->withCompletenesses = 'true';

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'product_z',
            'search_after' => ['69']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('identifier', Directions::ASCENDING)->shouldBeCalled();
        $pqb->addFilter('identifier', Operators::IS_NOT_EMPTY, null)->shouldBeCalled();

        $connectorProduct = new ConnectorProduct(
            Uuid::fromString('54162e35-ff81-48f1-96d5-5febd3f00fd5'),
            'identifier_5',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            [],
            new ReadValueCollection(),
            null,
            null
        );
        $connectorProductWithCompletenesses = $connectorProduct->buildWithCompletenesses(
            new ProductCompletenessCollection(
                Uuid::fromString('d9f573cc-8905-4949-8151-baf9d5328f26'),
                [
                    new ProductCompleteness('ecommerce', 'en_US', 10, 5),
                    new ProductCompleteness('ecommerce', 'fr_FR', 10, 1),
                ]
            )
        );

        $productList = new ConnectorProductList(1, [$connectorProduct]);
        $getConnectorProducts
            ->fromProductQueryBuilder($pqb, 1, null, null, null)
            ->willReturn($productList);

        $getProductsWithCompletenesses->fromConnectorProductList($productList, null, [])->willReturn(new ConnectorProductList(1, [$connectorProductWithCompletenesses]));

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductList(1, [$connectorProductWithCompletenesses]));
    }
}
