<?php

namespace spec\Pim\Bundle\EnrichBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Elasticsearch\FromSizeCursor;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

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
                            '_source' => ['identifier' => 'a-variant-product', 'document_type' => ProductInterface::class],
                            'sort' => ['pim_catalog_product#a-variant-product']
                        ],
                        [
                            '_source' => ['identifier' => 'a-sub-product-model', 'document_type' => ProductModelInterface::class],
                            'sort' => ['pim_catalog_product#a-sub-product-model']
                        ],
                    ]
                ]
            ]);

        $this->beConstructedWith(
            $esClient,
            $productRepository,
            $productModelRepository,
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
                            '_source' => ['identifier' => 'a-root-product-model', 'document_type' => ProductModelInterface::class],
                            'sort' => ['pim_catalog_product#a-root-product-model']
                        ],
                        [
                            '_source' => ['identifier' => 'a-product', 'document_type' => ProductInterface::class],
                            'sort' => ['pim_catalog_product#a-product']
                        ],
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
                            '_source' => ['identifier' => 'foo', 'document_type' => ProductModelInterface::class],
                            'sort' => ['pim_catalog_product#foo']
                        ],
                        [
                            '_source' => ['identifier' => 'foo', 'document_type' => ProductInterface::class],
                            'sort' => ['pim_catalog_product#foo']
                        ],
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
