<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\FromSizeCursor;
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

class FromSizeCursorSpec extends ObjectBehavior
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
            25,
            20,
            0
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FromSizeCursor::class);
        $this->shouldImplement(CursorInterface::class);
    }

    function it_is_countable(
        Client $esClient,
    ) {
        $this->shouldImplement(\Countable::class);
        $esClient->search([
            'track_total_hits' => true,
        ])->shouldBeCalledOnce()->willReturn([
            'hits' => [
                'total' => ['value' => 2, 'relation' => 'eq'],
                'hits' => [
                    [
                        '_source' => ['identifier' => 'a-root-product-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_67'],
                        'sort' => ['#product_model_67']
                    ],
                    [
                        '_source' => ['identifier' => 'a-sub-product-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_6'],
                        'sort' => ['#product_model_6']
                    ],
                ]
            ]
        ]);
        $this->count()->shouldReturn(2);
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

        $productRepository->getItemsFromUuids([$simpleUuid->toString(), $variantUuid->toString()])
            ->shouldBeCalledOnce()->willReturn([$variantProduct, $simpleProduct]);
        $productModelRepository->getItemsFromIdentifiers(['a-root-product-model', 'a-non-existing-model', 'a-sub-product-model'])
            ->shouldBeCalledOnce()->willReturn([$rootProductModel, $subProductModel]);

        $esClient->search(
            [
                'size' => 20,
                'sort' => ['id' => 'asc'],
                'from' => 0,
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 5, 'relation' => 'eq'],
                'hits' => [
                    [
                        '_source' => ['identifier' => 'a-root-product-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_10'],
                        'sort' => ['#product_model_10']
                    ],
                    [
                        '_source' => ['identifier' => 'a-product', 'document_type' => ProductInterface::class, 'id' => 'product_' . $simpleUuid->toString()],
                        'sort' => ['#product_' . $simpleUuid->toString()]
                    ],
                    [
                        '_source' => ['identifier' => 'a-non-existing-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_55'],
                        'sort' => ['#product_model_55']
                    ],
                    [
                        '_source' => ['identifier' => 'a-variant-product', 'document_type' => ProductInterface::class, 'id' => 'product_' . $variantUuid->toString()],
                        'sort' => ['#product_' . $variantUuid->toString()]
                    ],
                    [
                        '_source' => ['identifier' => 'a-sub-product-model', 'document_type' => ProductModelInterface::class, 'id' => 'product_model_24'],
                        'sort' => ['#product_model_24']
                    ],
                ]
            ]
        ]);

        $esClient->search(
            [
                'size' => 16,
                'sort' => ['id' => 'asc'],
                'from' => 4,
            ]
        )->shouldBeCalled()->willReturn([
            'hits' => [
                'total' => ['value' => 5, 'relation' => 'eq'],
                'hits' => []
            ]
        ]);

        $this->shouldIterateLike([$rootProductModel, $simpleProduct, $variantProduct, $subProductModel]);
    }
}
