<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductModelsQuery;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ListProductModelsQueryHandlerSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $channelRepository,
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        GetConnectorProductModels $connectorProductModels
    ) {
        $this->beConstructedWith(
            new ApplyProductSearchQueryParametersToPQB($channelRepository->getWrappedObject()),
            $fromSizePqbFactory,
            $searchAfterPqbFactory,
            $primaryKeyEncrypter,
            $connectorProductModels,
            $channelRepository
        );
    }

    function it_creates_a_pqb_for_offset(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::OFFSET;
        $query->limit = 42;
        $query->page = 69;

        $fromSizePqbFactory->create([
            'limit' => 42,
            'from' => 2856
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);

        $searchAfterPqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query);
    }

    function it_creates_a_pqb_for_search_after(
        ProductQueryBuilderFactoryInterface $fromSizePqbFactory,
        ProductQueryBuilderFactoryInterface $searchAfterPqbFactory,
        PrimaryKeyEncrypter $primaryKeyEncrypter,
        ProductQueryBuilderInterface $pqb,
        CursorInterface $cursor,
        GetConnectorProductModels $getConnectorProductModels
    ) {
        $query = new ListProductModelsQuery();
        $query->paginationType = PaginationTypes::SEARCH_AFTER;
        $query->limit = 42;
        $query->searchAfter = '69';

        $primaryKeyEncrypter->decrypt('69')->shouldBeCalled()->willReturn('encoded69');

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'encoded69',
            'search_after' => ['encoded69']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();
        $pqb->execute()->shouldBeCalled()->willReturn($cursor);
        $cursor->count()->willReturn(0);
        $cursor->rewind()->shouldBeCalled();

        $getConnectorProductModels->fromProductModelCodes(Argument::cetera(), null, null, null)->willReturn([]);

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(new ConnectorProductModelList(0, []));

    }
}
