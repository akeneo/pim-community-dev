<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductModelDescendantsSaver;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Prophecy\Argument;

class ProductModelDescendantsSaverSpec extends ObjectBehavior
{
    function let(
        ObjectManager $objectManager,
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        CompletenessManager $completenessManager,
        BulkIndexerInterface $productIndexer,
        BulkIndexerInterface $bulkProductModelIndexer,
        IndexerInterface $productModelIndexer
    ) {
        $this->beConstructedWith(
            $objectManager,
            $productModelRepository,
            $pqbFactory,
            $completenessManager,
            $productIndexer,
            $bulkProductModelIndexer,
            $productModelIndexer
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsSaver::class);
    }

    function it_computes_completeness_and_indexes_a_product_model_descendants_which_are_products_and_sub_product_models(
        $productModelRepository,
        $pqbFactory,
        $completenessManager,
        $objectManager,
        $productIndexer,
        $bulkProductModelIndexer,
        $productModelIndexer,
        ProductQueryBuilderInterface $pqb,
        ProductModelInterface $productModel,
        ProductModelInterface $productModelsChildren,
        ProductInterface $variantProduct1,
        ProductInterface $variantProduct2,
        CursorInterface $cursor
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $productModelRepository->findDescendantProductIdentifiers($productModel)
            ->willReturn(['product_1', 'product_2']);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('identifier', Operators::IN_LIST, ['product_1', 'product_2'])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->valid()->willReturn(true, true, false, true, true, false);
        $cursor->count()->willReturn(2);
        $cursor->rewind()->shouldBeCalled();
        $cursor->current()->willReturn($variantProduct1, $variantProduct2, $variantProduct1, $variantProduct2);
        $cursor->next()->shouldBeCalled();

        $completenessManager->schedule($variantProduct1)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($variantProduct1)->shouldBeCalled();
        $objectManager->persist($variantProduct1)->shouldBeCalled();

        $completenessManager->schedule($variantProduct2)->shouldBeCalled();
        $completenessManager->generateMissingForProduct($variantProduct2)->shouldBeCalled();
        $objectManager->persist($variantProduct2)->shouldBeCalled();

        $objectManager->flush()->shouldBeCalled();

        $productIndexer->indexAll([$variantProduct1, $variantProduct2])->shouldBeCalled();

        $productModelRepository->findChildrenProductModels($productModel)->willReturn([$productModelsChildren]);
        $bulkProductModelIndexer->indexAll([$productModelsChildren]);

        $productModelIndexer->index($productModel);

        $this->save($productModel);
    }

    function it_does_not_fail_when_product_model_has_no_child(
        $productModelRepository,
        $pqbFactory,
        $completenessManager,
        $objectManager,
        $productIndexer,
        $bulkProductModelIndexer,
        $productModelIndexer,
        ProductQueryBuilderInterface $pqb,
        ProductModelInterface $productModel,
        CursorInterface $cursor
    ) {
        $productModel->getCode()->willReturn('product_model_code');
        $productModelRepository->findDescendantProductIdentifiers($productModel)
            ->willReturn([]);

        $pqbFactory->create()->willReturn($pqb);
        $pqb->addFilter('identifier', Operators::IN_LIST, [])->shouldBeCalled();
        $pqb->execute()->willReturn($cursor);

        $cursor->count()->willReturn(0);

        $completenessManager->schedule(Argument::cetera())->shouldNotBeCalled();
        $completenessManager->generateMissingForProduct(Argument::cetera())->shouldNotBeCalled();

        $objectManager->persist(Argument::cetera())->shouldNotBeCalled();
        $objectManager->flush()->shouldNotBeCalled();

        $productIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $productModelRepository->findChildrenProductModels($productModel)->willReturn([]);
        $bulkProductModelIndexer->indexAll(Argument::cetera())->shouldNotBeCalled();

        $productModelIndexer->index($productModel);

        $this->save($productModel);
    }

    function it_throws_when_we_dont_save_a_product_model(
        \stdClass $wrongObject
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', [$wrongObject]);
    }
}
