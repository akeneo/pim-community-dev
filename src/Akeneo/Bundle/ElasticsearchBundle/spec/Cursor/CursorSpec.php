<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Prophecy\Argument;

class CursorSpec extends ObjectBehavior
{
    function let(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        ProductInterface $productFoo,
        ProductInterface $productBar,
        ProductInterface $productBaz
    ) {
        $data = [$productFoo, $productBar, $productBaz];
        $repository->getItemsFromIdentifiers(['foo', 'bar', 'baz'])->willReturn($data);

        $esClient->search('pim_catalog_product', ['size' => 3, 'sort' => ['updated' => 'desc', '_uid' => 'asc']])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        ['_source' => ['identifier' => 'foo']],
                        ['_source' => ['identifier' => 'bar']],
                        ['_source' => ['identifier' => 'baz']]
                    ]
                ]
            ]);

        $this->beConstructedWith($esClient, $repository, ['sort' => ['updated' => 'desc']], 'pim_catalog_product', 3);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\ElasticsearchBundle\Cursor\Cursor');
        $this->shouldImplement('Akeneo\Component\StorageUtils\Cursor\CursorInterface');
    }

    function it_is_countable()
    {
        $this->shouldImplement('\Countable');
        $this->shouldHaveCount(4);
    }

    function it_is_iterable($repository, $esClient, $productFoo, $productBar, $productBaz, ProductInterface $productFum)
    {
        $esClient->search('pim_catalog_product', ['size' => 3, 'sort' => ['updated' => 'desc', '_uid' => 'asc'], 'search_after' => ['pim_catalog_product#baz']])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        ['_source' => ['identifier' => 'fum']]
                    ]
                ]
            ]);
        $esClient->search('pim_catalog_product', ['size' => 3, 'sort' => ['updated' => 'desc', '_uid' => 'asc'], 'search_after' => ['pim_catalog_product#fum']])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => []
                ]
            ]);

        $page1 = [$productFoo, $productBar, $productBaz];
        $page2 = [$productFum];
        $data = array_merge($page1, $page2);

        $this->shouldImplement('\Iterator');

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
