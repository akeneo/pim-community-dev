<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\FromSizeCursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

class FromSizeCursorSpec extends ObjectBehavior
{
    function let(
        Client $esClient,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        ProductInterface $variantProduct,
        ProductModelInterface $subProductModel
    ) {
        $variantProduct->getIdentifier()->willReturn('a-variant-product');
        $productRepository->getItemsFromIdentifiers(['a-variant-product'])->willReturn([$variantProduct]);

        $subProductModel->getCode()->willReturn('a-sub-product-model');
        $productModelRepository->getItemsFromIdentifiers(['a-sub-product-model'])->willReturn([$subProductModel]);

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
                            '_source' => ['identifier' => 'a-variant-product', 'document_type' => ProductInterface::class],
                            'sort' => ['#a-variant-product']
                        ],
                        [
                            '_source' => ['identifier' => 'a-sub-product-model', 'document_type' => ProductModelInterface::class],
                            'sort' => ['#a-sub-product-model']
                        ],
                    ]
                ]
            ]);

        $this->beConstructedWith(
            $esClient,
            $productRepository,
            $productModelRepository,
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
        $esClient,
        $variantProduct,
        $subProductModel,
        $productRepository,
        $productModelRepository,
        ProductInterface $product,
        ProductModelInterface $rootProductModel
    ) {
        $product->getIdentifier()->willReturn('a-product');
        $productRepository->getItemsFromIdentifiers(['a-product'])->willReturn([$product]);

        $rootProductModel->getCode()->willReturn('a-root-product-model');
        $productModelRepository->getItemsFromIdentifiers(['a-root-product-model'])->willReturn([$rootProductModel]);

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
                            '_source' => ['identifier' => 'a-root-product-model', 'document_type' => ProductModelInterface::class],
                            'sort' => ['#a-root-product-model']
                        ],
                        [
                            '_source' => ['identifier' => 'a-product', 'document_type' => ProductInterface::class],
                            'sort' => ['#a-product']
                        ],
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

        $page1 = [$variantProduct, $subProductModel];
        $page2 = [$rootProductModel, $product];
        $data = array_merge($page1, $page2);

        $this->shouldImplement(\Iterator::class);

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

    function it_is_iterable_with_products_and_product_models_having_the_same_identifiers(
        $esClient,
        $variantProduct,
        $subProductModel,
        $productRepository,
        $productModelRepository,
        ProductInterface $product,
        ProductModelInterface $rootProductModel
    ) {
        $product->getIdentifier()->willReturn('foo');
        $productRepository->getItemsFromIdentifiers(['foo'])->willReturn([$product]);

        $rootProductModel->getCode()->willReturn('foo');
        $productModelRepository->getItemsFromIdentifiers(['foo'])->willReturn([$rootProductModel]);

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
                            '_source' => ['identifier' => 'foo', 'document_type' => ProductModelInterface::class],
                            'sort' => ['#foo']
                        ],
                        [
                            '_source' => ['identifier' => 'foo', 'document_type' => ProductInterface::class],
                            'sort' => ['#foo']
                        ],
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

        $page1 = [$variantProduct, $subProductModel];
        $page2 = [$rootProductModel, $product];
        $data = array_merge($page1, $page2);

        $this->shouldImplement(\Iterator::class);

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
