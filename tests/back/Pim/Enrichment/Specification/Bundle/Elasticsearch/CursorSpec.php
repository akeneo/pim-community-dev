<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Cursor;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class CursorSpec extends ObjectBehavior
{
    function let(
        Client $esClient,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
    ) {
        $this->beConstructedWith(
            $esClient,
            $productRepository,
            $productModelRepository,
            [],
            2
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Cursor::class);
        $this->shouldImplement(CursorInterface::class);
    }

    function it_is_countable(
        Client $esClient,
    ) {
        $this->shouldImplement(\Countable::class);
        $simpleUuid = Uuid::uuid4();
        $variantUuid = Uuid::uuid4();
        $variantProduct = new Product($variantUuid);
        $variantProduct->setIdentifier('a-variant-product');

        $esClient->search([
            'track_total_hits' => true,
        ])->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 4, 'relation' => 'eq'],
                'hits' => [
                    [
                        '_source' => [
                            'identifier' => 'a-variant-product',
                            'document_type' => ProductInterface::class,
                            'id' => 'product_' . $variantUuid->toString(),
                        ],
                        'sort' => ['#product_' . $variantUuid->toString()],
                    ],
                    [
                        '_source' => [
                            'identifier' => null,
                            'document_type' => ProductInterface::class,
                            'id' => 'product_' . $simpleUuid->toString(),
                        ],
                        'sort' => ['#product_' . $simpleUuid->toString()],
                    ],
                ],
            ],
        ]);
        $this->count()->shouldReturn(4);
    }

    function it_is_iterable(
        Client $esClient,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
    ) {
        $simpleUuid = Uuid::uuid4();
        $simpleProduct = new Product($simpleUuid);
        $variantUuid = Uuid::uuid4();
        $variantProduct = new Product($variantUuid);
        $variantProduct->setIdentifier('a-variant-product');

        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('a-root-product-model');
        $subProductModel = new ProductModel();
        $subProductModel->setCode('a-sub-product-model');

        $productRepository->getItemsFromUuids([$simpleUuid->toString()])->shouldBeCalled()->willReturn(
            [$simpleProduct]
        );
        $productRepository->getItemsFromUuids([$variantUuid->toString()])->shouldBeCalled()->willReturn(
            [$variantProduct]
        );
        $productModelRepository->getItemsFromIdentifiers(['a-root-product-model'])->shouldBeCalled()->willReturn(
            [$rootProductModel]
        );
        $productModelRepository->getItemsFromIdentifiers(['a-sub-product-model'])->shouldBeCalled()->willReturn(
            [$subProductModel]
        );

        $esClient->search([
            'size' => 2,
            'sort' => ['id' => 'asc'],
        ])->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 4, 'relation' => 'eq'],
                'hits' => [
                    [
                        '_source' => [
                            'identifier' => 'a-variant-product',
                            'document_type' => ProductInterface::class,
                            'id' => 'product_' . $variantProduct->getUuid()->toString(),
                        ],
                        'sort' => ['#a-variant-product'],
                    ],
                    [
                        '_source' => [
                            'identifier' => 'a-sub-product-model',
                            'document_type' => ProductModelInterface::class,
                            'id' => 'product_model_42',
                        ],
                        'sort' => ['#a-sub-product-model'],
                    ],
                ],
            ],
        ]);
        $esClient->search([
            'size' => 2,
            'sort' => ['id' => 'asc'],
            'search_after' => ['#a-sub-product-model'],
        ])->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 4, 'relation' => 'eq'],
                'hits' => [
                    [
                        '_source' => [
                            'identifier' => 'a-simple-product',
                            'document_type' => ProductInterface::class,
                            'id' => 'product_' . $simpleProduct->getUuid()->toString(),
                        ],
                        'sort' => ['#a-simple-product'],
                    ],
                    [
                        '_source' => [
                            'identifier' => 'a-root-product-model',
                            'document_type' => ProductModelInterface::class,
                            'id' => 'product_model_55',
                        ],
                        'sort' => ['#a-root-product-model'],
                    ],
                ],
            ],
        ]);
        $esClient->search(
            [
                'size' => 2,
                'sort' => ['id' => 'asc'],
                'search_after' => ['#a-root-product-model'],
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 4],
                'hits' => [],
            ],
        ]);

        $this->shouldImplement(\Iterator::class);

        $this->shouldIterateLike([$variantProduct, $subProductModel, $simpleProduct, $rootProductModel]);
    }

    /**
     * PIM-10232
     */
    function it_is_iterable_and_returns_page_size_results(
        Client $esClient,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
    ) {
        $simpleUuid = Uuid::uuid4();
        $simpleProduct = new Product($simpleUuid);
        $variantUuid = Uuid::uuid4();
        $variantProduct = new Product($variantUuid);
        $variantProduct->setIdentifier('a-variant-product');

        $rootProductModel = new ProductModel();
        $rootProductModel->setCode('a-root-product-model');
        $subProductModel = new ProductModel();
        $subProductModel->setCode('a-sub-product-model');

        $nonExistingUuid = Uuid::uuid4();

        $productRepository->getItemsFromUuids([$variantUuid->toString(), $nonExistingUuid->toString()])
            ->shouldBeCalledOnce()->willReturn([$variantProduct]);
        $productRepository->getItemsFromUuids([$simpleUuid->toString()])
            ->shouldBeCalledOnce()->willReturn([$simpleProduct]);
        $productRepository->getItemsFromUuids([])
            ->shouldBeCalledOnce()->willReturn([]);
        $productModelRepository->getItemsFromIdentifiers(['a-sub-product-model'])
            ->shouldBeCalledOnce()->willReturn([$subProductModel]);
        $productModelRepository->getItemsFromIdentifiers(['a-root-product-model'])
            ->shouldBeCalledOnce()->willReturn([$rootProductModel]);
        $productModelRepository->getItemsFromIdentifiers([])
            ->shouldBeCalledOnce()->willReturn([]);

        $esClient->search(
            [
                'size' => 2,
                'sort' => ['id' => 'asc'],
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 6],
                'hits' => [
                    [
                        '_source' => ['identifier' => 'a-variant-product', 'document_type' => ProductInterface::class, 'id' => 'product_' . $variantUuid->toString()],
                        'sort' => ['#product_' . $variantUuid->toString()]
                    ],
                    [
                        '_source' => ['identifier' => 'a-non-existing-product', 'document_type' => ProductInterface::class, 'id' => 'product_' . $nonExistingUuid->toString()],
                        'sort' => ['#product_' . $nonExistingUuid->toString()]
                    ],
                ]
            ]
        ]);
        $esClient->search(
            [
                'size' => 1,
                'sort' => ['id' => 'asc'],
                'search_after' => ['#product_' . $nonExistingUuid->toString()],
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 6],
                'hits' => [
                    [
                        '_source' => ['identifier' => 'a-sub-product-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_55'],
                        'sort' => ['#product_model_55']
                    ],
                ]
            ]
        ]);
        $esClient->search(
            [
                'size' => 2,
                'sort' => ['id' => 'asc'],
                'search_after' => ['#product_model_55'],
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 6],
                'hits' => [
                    [
                        '_source' => ['identifier' => 'a-root-product-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_42'],
                        'sort' => ['#product_model_42']
                    ],
                    [
                        '_source' => ['identifier' => null, 'document_type' => ProductInterface::class, 'id' => 'product_' . $simpleUuid->toString()],
                        'sort' => ['#product_' . $simpleUuid->toString()],
                    ],
                ],
            ],
        ]);
        $esClient->search(
            [
                'size' => 2,
                'sort' => ['id' => 'asc'],
                'search_after' => ['#product_' . $simpleUuid->toString()],
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 6],
                'hits' => [],
            ],
        ]);

        $this->shouldIterateLike([$variantProduct, $subProductModel, $rootProductModel, $simpleProduct]);
    }
}
