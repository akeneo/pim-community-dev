<?php

declare(strict_types=1);

namespace spec\Akeneo\SharedCatalog\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use Akeneo\SharedCatalog\Model\SharedCatalog;
use Akeneo\SharedCatalog\Query\FindProductIdentifiersQuery;
use Akeneo\SharedCatalog\Query\GetProductIdFromProductIdentifierQueryInterface;
use PhpSpec\ObjectBehavior;

class FindProductIdentifiersQuerySpec extends ObjectBehavior
{
    public function let(
        GetProductIdFromProductIdentifierQueryInterface $getProductIdFromProductIdentifierQuery,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
    ) {
        $this->beConstructedWith(
            $getProductIdFromProductIdentifierQuery,
            $productQueryBuilderFactory
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(FindProductIdentifiersQuery::class);
    }

    public function it_requires_the_limit_option(SharedCatalog $sharedCatalog)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('find', [
            $sharedCatalog,
            [],
        ]);
    }

    public function it_requires_a_valid_limit_option(SharedCatalog $sharedCatalog)
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('find', [
            $sharedCatalog,
            [
                'limit' => 'foo',
            ],
        ]);
    }

    public function it_throws_an_exception_if_the_search_after_identifier_does_not_exists(SharedCatalog $sharedCatalog)
    {
        $sharedCatalog->getPQBFilters()->willReturn([]);
        $sharedCatalog->getDefaultScope()->willReturn('ecommerce');

        $this->shouldThrow(\InvalidArgumentException::class)->during('find', [
            $sharedCatalog,
            [
                'limit' => 100,
                'search_after' => 'does_not_exists',
            ],
        ]);
    }

    public function it_returns_an_array_of_identifiers(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderInterface $productQueryBuilder,
        SharedCatalog $sharedCatalog
    ) {
        $sharedCatalog->beConstructedWith([
            'shared_catalog',
            'julia',
            [],
            [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                'structure' => [
                    'scope' => 'ecommerce',
                ],
            ],
            [],
        ]);
        $sharedCatalog->getDefaultScope()->willReturn('ecommerce');
        $sharedCatalog->getPQBFilters()->willReturn([
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
        ]);
        $productQueryBuilderFactory
            ->create([
                'default_scope' => 'ecommerce',
                'filters' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                'limit' => 100,
            ])
            ->willReturn($productQueryBuilder);
        $productQueryBuilder
            ->addSorter('identifier', Directions::ASCENDING)
            ->willReturn();
        $productQueryBuilder
            ->execute()
            ->willReturn(new \ArrayIterator([
                new IdentifierResult('1111111225', ProductInterface::class),
                new IdentifierResult('1111111226', ProductInterface::class),
                new IdentifierResult('1111111227', ProductInterface::class),
            ]));

        $this
            ->find(
                $sharedCatalog,
                [
                    'limit' => 100,
                ]
            )
            ->shouldEqual([
                '1111111225',
                '1111111226',
                '1111111227',
            ]);
    }

    public function it_returns_an_array_of_identifiers_matching_a_search_after(
        GetProductIdFromProductIdentifierQueryInterface $getProductIdFromProductIdentifierQuery,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ProductQueryBuilderInterface $productQueryBuilder,
        SharedCatalog $sharedCatalog
    ) {
        $searchAfterProductIdentifier = 'identifier_WITH_UPPERCASE';

        $sharedCatalog->beConstructedWith([
            'shared_catalog',
            'julia',
            [],
            [
                'data' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                'structure' => [
                    'scope' => 'ecommerce',
                ],
            ],
            [],
        ]);
        $sharedCatalog->getDefaultScope()->willReturn('ecommerce');
        $sharedCatalog->getPQBFilters()->willReturn([
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
        ]);
        $getProductIdFromProductIdentifierQuery
            ->execute($searchAfterProductIdentifier)
            ->willReturn('42');
        $productQueryBuilderFactory
            ->create([
                'default_scope' => 'ecommerce',
                'filters' => [
                    [
                        'field' => 'enabled',
                        'operator' => '=',
                        'value' => true,
                    ],
                ],
                'limit' => 100,
                'search_after' => [
                    'identifier_with_uppercase', // elasticsearch expect the search after to be in lowercase
                    'product_42',
                ],
            ])
            ->willReturn($productQueryBuilder);
        $productQueryBuilder
            ->addSorter('identifier', Directions::ASCENDING)
            ->willReturn();
        $productQueryBuilder
            ->execute()
            ->willReturn(new \ArrayIterator([
                new IdentifierResult('1111111228', ProductInterface::class),
                new IdentifierResult('1111111229', ProductInterface::class),
                new IdentifierResult('1111111230', ProductInterface::class),
            ]));

        $this
            ->find(
                $sharedCatalog,
                [
                    'limit' => 100,
                    'search_after' => $searchAfterProductIdentifier,
                ]
            )
            ->shouldEqual([
                '1111111228',
                '1111111229',
                '1111111230',
            ]);
    }
}
