<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\Cursor;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class CursorSpec extends ObjectBehavior
{
    function let(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        ProductInterface $productFoo,
        ProductInterface $productBar,
        ProductInterface $productBaz
    ) {
        $productFoo->getIdentifier()->willReturn('foo');
        $productBaz->getIdentifier()->willReturn('baz');
        $productBar->getIdentifier()->willReturn('bar');
        $data = [$productFoo, $productBar, $productBaz];
        $repository->getItemsFromIdentifiers(['foo', 'bar', 'baz'])->willReturn($data);

        $esClient->search(['track_total_hits' => true, 'size' => 3, 'sort' => ['updated' => 'desc', '_id' => 'asc']])
            ->willReturn([
                'hits' => [
                    'total' => ['value' => 4, 'relation' => 'eq'],
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'foo'],
                            'sort' => [1490810553000, '#foo']
                        ],
                        [
                            '_source' => ['identifier' => 'bar'],
                            'sort' => [1490810554000, '#bar']
                        ],
                        [
                            '_source' => ['identifier' => 'baz'],
                            'sort' => [1490810555000, '#baz']
                        ]
                    ]
                ]
            ]);

        $this->beConstructedWith($esClient, $repository, ['sort' => ['updated' => 'desc']], 3);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Cursor::class);
        $this->shouldImplement(CursorInterface::class);
    }

    function it_is_countable()
    {
        $this->shouldImplement(\Countable::class);
        $this->count()->shouldReturn(4);
    }

    function it_is_iterable(
        $repository,
        $esClient,
        $productFoo,
        $productBar,
        $productBaz,
        ProductInterface $productFum
    ) {
        $esClient->search(
            [
                'track_total_hits' => true,
                'size' => 3,
                'sort' => ['updated' => 'desc', '_id' => 'asc'],
                'search_after' => [1490810555000, '#baz']
            ])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'fum'],
                            'sort' => [1490810565000, '#fum']
                        ]
                    ]
                ]
            ]);
        $esClient->search(
            [
                'track_total_hits' => true,
                'size' => 3,
                'sort' => ['updated' => 'desc', '_id' => 'asc'],
                'search_after' => [1490810565000, '#fum']
            ])->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => []
                ]
            ]);

        $page1 = [$productFoo, $productBar, $productBaz];
        $page2 = [$productFum];
        $data = array_merge($page1, $page2);

        $this->shouldImplement(\Iterator::class);

        $repository->getItemsFromIdentifiers(['fum'])->willReturn($page2);
        $productBaz->getIdentifier()->willReturn('baz');
        $productFum->getIdentifier()->willReturn('fum');

        $this->rewind()->shouldReturn(null);
        for ($i = 0; $i < 4; $i++) {
            if ($i > 0) {
                $this->next()->shouldReturn(null);
            }
            $this->valid()->shouldReturn(true);
            $this->current()->shouldReturn($data[$i]);

            $n = 0 === $i%3 ? 0 : $i;
            $this->key()->shouldReturn($n);
        }

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(false);

        // check behaviour after the end of data
        $this->current()->shouldReturn(null);
        $this->key()->shouldReturn(null);
    }
}
