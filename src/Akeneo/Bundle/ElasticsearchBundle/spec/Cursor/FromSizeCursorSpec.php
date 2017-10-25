<?php

namespace spec\Akeneo\Bundle\ElasticsearchBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Cursor\FromSizeCursor;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;

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

        $esClient->search('pim_catalog_product', [
            'from' => 0,
            'size' => 2,
            'sort' => ['_uid' => 'asc']
        ])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'baz'],
                            'sort' => ['pim_catalog_product#baz']
                        ],
                        [
                            '_source' => ['identifier' => 'foo'],
                            'sort' => ['pim_catalog_product#foo']
                        ],
                    ]
                ]
            ]);

        $this->beConstructedWith(
            $esClient,
            $repository,
            [],
            'pim_catalog_product',
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
            'pim_catalog_product',
            [
                'size' => 2,
                'sort' => ['_uid' => 'asc'],
                'from' => 2
            ])
            ->willReturn([
                'hits' => [
                    'total' => 4,
                    'hits' => [
                        [
                            '_source' => ['identifier' => 'fum'],
                            'sort' => ['pim_catalog_product#fum']
                        ]
                    ]
                ]
            ]);
        $esClient->search(
            'pim_catalog_product',
            [
                'size' => 2,
                'sort' => ['_uid' => 'asc'],
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
        $this->current()->shouldReturn(false);
        $this->key()->shouldReturn(null);
    }
}
