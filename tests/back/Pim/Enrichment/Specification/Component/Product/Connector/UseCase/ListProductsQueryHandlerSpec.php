<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ApplyProductSearchQueryParametersToPQB;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\ListProductsQuery;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\Tool\Component\Api\Pagination\PaginationTypes;
use Akeneo\Tool\Component\Api\Security\PrimaryKeyEncrypter;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
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

        $fromSizePqbFactory->create([
            'limit' => 42,
            'from' => 2856
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();
        $pqb->execute()->willReturn(new class implements CursorInterface {
            private $identifierResults;

            public function __construct()
            {
                $identifierResultsArrayCOllection = new ArrayCollection([
                    new IdentifierResult('identifier_1', ProductInterface::class),
                    new IdentifierResult('identifier_2', ProductInterface::class)
                ]);
                $this->identifierResults = $identifierResultsArrayCOllection->getIterator();
            }
            public function current() { return $this->identifierResults->current(); }
            public function next() { $this->identifierResults->next(); }
            public function key() { return $this->identifierResults->key(); }
            public function count() { return 2; }
            public function valid() { return $this->identifierResults->valid(); }
            public function rewind() { $this->identifierResults->rewind(); }
        });

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
            new ValueCollection()
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
            new ValueCollection()
        );

        $getConnectorProducts
            ->fromProductIdentifiers(['identifier_1', 'identifier_2'], ['name'], 'tablet', ['en_US'])
            ->willReturn([$connectorProduct1, $connectorProduct2]);

        $searchAfterPqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(
            new ConnectorProductList(
                2,
                [$connectorProduct1, $connectorProduct2]
            )
        );
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

        $primaryKeyEncrypter->decrypt('69')->shouldBeCalled()->willReturn('encoded69');

        $searchAfterPqbFactory->create([
            'limit' => 42,
            'search_after_unique_key' => 'encoded69',
            'search_after' => ['encoded69']
        ])->shouldBeCalled()->willReturn($pqb);

        $pqb->addSorter('id', Directions::ASCENDING)->shouldBeCalled();
        $pqb->execute()->willReturn(new class implements CursorInterface {
            private $identifierResults;

            public function __construct()
            {
                $identifierResultsArrayCOllection = new ArrayCollection([
                    new IdentifierResult('identifier_1', ProductInterface::class),
                    new IdentifierResult('identifier_2', ProductInterface::class)
                ]);
                $this->identifierResults = $identifierResultsArrayCOllection->getIterator();
            }
            public function current() { return $this->identifierResults->current(); }
            public function next() { $this->identifierResults->next(); }
            public function key() { return $this->identifierResults->key(); }
            public function count() { return 2; }
            public function valid() { return $this->identifierResults->valid(); }
            public function rewind() { $this->identifierResults->rewind(); }
        });

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
            new ValueCollection()
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
            new ValueCollection()
        );

        $getConnectorProducts
            ->fromProductIdentifiers(['identifier_1', 'identifier_2'], null, null, null)
            ->willReturn([$connectorProduct1, $connectorProduct2]);

        $fromSizePqbFactory->create(Argument::cetera())->shouldNotBeCalled();

        $this->handle($query)->shouldBeLike(
            new ConnectorProductList(
                2,
                [$connectorProduct1, $connectorProduct2]
            )
        );
    }
}
