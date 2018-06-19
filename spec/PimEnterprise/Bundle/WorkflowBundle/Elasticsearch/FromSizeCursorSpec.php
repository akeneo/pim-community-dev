<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\FromSizeCursor;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Model\ProductModelDraft;

class FromSizeCursorSpec extends ObjectBehavior
{
    function let(
        Client $esClient,
        CursorableRepositoryInterface $productDraftRepository,
        CursorableRepositoryInterface $productModelDraftRepository,
        ProductDraft $variantProductDraft,
        ProductModelDraft $subProductModelDraft
    ) {
        $variantProductDraft->getIdentifier()->willReturn('a-variant-product');
        $productDraftRepository->getItemsFromIdentifiers(['a-variant-product'])->willReturn([$variantProductDraft]);

        $subProductModelDraft->getIdentifier()->willReturn('a-sub-product-model');
        $productModelDraftRepository->getItemsFromIdentifiers(['a-sub-product-model'])->willReturn([$subProductModelDraft]);

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
                            '_source' => ['identifier' => 'a-variant-product', 'document_type' => ProductDraft::class],
                            'sort' => ['pim_catalog_product#a-variant-product']
                        ],
                        [
                            '_source' => ['identifier' => 'a-sub-product-model', 'document_type' => ProductModelDraft::class],
                            'sort' => ['pim_catalog_product#a-sub-product-model']
                        ],
                    ]
                ]
            ]);

        $this->beConstructedWith(
            $esClient,
            $productDraftRepository,
            $productModelDraftRepository,
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
        $variantProductDraft,
        $subProductModelDraft,
        $productDraftRepository,
        $productModelDraftRepository,
        ProductDraft $productDraft,
        ProductModelDraft $rootProductModelDraft
    ) {
        $productDraft->getIdentifier()->willReturn('a-product');
        $productDraftRepository->getItemsFromIdentifiers(['a-product'])->willReturn([$productDraft]);

        $rootProductModelDraft->getIdentifier()->willReturn('a-root-product-model');
        $productModelDraftRepository->getItemsFromIdentifiers(['a-root-product-model'])->willReturn([$rootProductModelDraft]);

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
                            '_source' => ['identifier' => 'a-root-product-model', 'document_type' => ProductDraft::class],
                            'sort' => ['pim_catalog_product#a-root-product-model']
                        ],
                        [
                            '_source' => ['identifier' => 'a-product', 'document_type' => ProductModelDraft::class],
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

        $page1 = [$variantProductDraft, $subProductModelDraft];
        $page2 = [$rootProductModelDraft, $productDraft];
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
        $variantProductDraft,
        $subProductModelDraft,
        $productDraftRepository,
        $productModelDraftRepository,
        ProductDraft $productDraft,
        ProductModelDraft $rootProductModelDraft
    ) {
        $productDraft->getIdentifier()->willReturn('foo');
        $productDraftRepository->getItemsFromIdentifiers(['foo'])->willReturn([$productDraft]);

        $rootProductModelDraft->getIdentifier()->willReturn('foo');
        $productModelDraftRepository->getItemsFromIdentifiers(['foo'])->willReturn([$rootProductModelDraft]);

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
                            '_source' => ['identifier' => 'foo', 'document_type' => ProductModelDraft::class],
                            'sort' => ['pim_catalog_product#foo']
                        ],
                        [
                            '_source' => ['identifier' => 'foo', 'document_type' => ProductDraft::class],
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

        $page1 = [$variantProductDraft, $subProductModelDraft];
        $page2 = [$rootProductModelDraft, $productDraft];
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
