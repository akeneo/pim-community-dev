<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Cursor\Cursor;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

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

        $esClient->search('pim_catalog_product', ['size' => 3, 'sort' => ['updated' => 'desc', '_uid' => 'asc']])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'foo'],
                            'sort' => [1490810553000, 'pim_catalog_product#foo']
                        ],
                        [
                            '_source' => ['identifier' => 'bar'],
                            'sort' => [1490810554000, 'pim_catalog_product#bar']
                        ],
                        [
                            '_source' => ['identifier' => 'baz'],
                            'sort' => [1490810555000, 'pim_catalog_product#baz']
                        ]
                    ]
                ]
            ]);

        $this->beConstructedWith($esClient, $repository, ['sort' => ['updated' => 'desc']], 'pim_catalog_product', 3);
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
            'pim_catalog_product',
            [
                'size' => 3,
                'sort' => ['updated' => 'desc', '_uid' => 'asc'],
                'search_after' => [1490810555000, 'pim_catalog_product#baz']
            ])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'fum'],
                            'sort' => [1490810565000, 'pim_catalog_product#fum']
                        ]
                    ]
                ]
            ]);
        $esClient->search(
            'pim_catalog_product',
            [
                'size' => 3,
                'sort' => ['updated' => 'desc', '_uid' => 'asc'],
                'search_after' => [1490810565000, 'pim_catalog_product#fum']
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
        $this->current()->shouldReturn(false);
        $this->key()->shouldReturn(null);
    }
}
