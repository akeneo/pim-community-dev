<?php

namespace spec\Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\FromSizeCursor;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

class FromSizeCursorSpec extends ObjectBehavior
{
    function let(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        ProductInterface $productFoo,
        ProductInterface $productBaz
    ) {
        $productFoo->getIdentifier()->willReturn('foo');
        $productBaz->getIdentifier()->willReturn('baz');
        $data = [$productBaz, $productFoo];
        $repository->getItemsFromIdentifiers(['baz', 'foo'])->willReturn($data);

        $esClient->search([
            'from' => 0,
            'size' => 2,
            'sort' => ['_id' => 'asc'],
            'track_total_hits' => true,
        ])
            ->willReturn([
                'hits' => [
                    'total' => ['value' => 4, 'relation' => 'eq'],
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'baz'],
                            'sort' => ['#baz']
                        ],
                        [
                            '_source' => ['identifier' => 'foo'],
                            'sort' => ['#foo']
                        ],
                    ]
                ]
            ]);

        $this->beConstructedWith(
            $esClient,
            $repository,
            [],
            3,
            2,
            0
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FromSizeCursor::class);
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
        $productBaz,
        ProductInterface $productFum
    ) {
        $esClient->search(
            [
                'size' => 2,
                'sort' => ['_id' => 'asc'],
                'from' => 2
            ])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'fum'],
                            'sort' => ['#fum']
                        ]
                    ]
                ]
            ]);
        $esClient->search(
            [
                'size' => 2,
                'sort' => ['_id' => 'asc'],
                'from' => 3
            ])->willReturn([
            'hits' => [
                'total' => 4,
                'hits' => []
            ]
        ]);

        $page1 = [$productBaz, $productFoo];
        $page2 = [$productFum];
        $data = array_merge($page1, $page2);

        $this->shouldImplement(\Iterator::class);

        $repository->getItemsFromIdentifiers(['fum'])->willReturn($page2);
        $productBaz->getIdentifier()->willReturn('baz');
        $productFum->getIdentifier()->willReturn('fum');

        for ($i = 0; $i < 2; $i++) {
            if ($i > 0) {
                $this->next()->shouldReturn(null);
            }
            $this->valid()->shouldReturn(true);
            $this->current()->shouldReturn($data[$i]);

            $n = 0 === $i%2 ? 0 : $i;
            $this->key()->shouldReturn($n);
        }

        $this->next()->shouldReturn(null);
        $this->valid()->shouldReturn(false);

        // check behaviour after the end of data
        $this->current()->shouldReturn(null);
        $this->key()->shouldReturn(null);
    }
}
