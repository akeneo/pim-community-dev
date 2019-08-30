<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\ProductModelDescendantsSaver;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Prophecy\Argument;

class ProductModelDescendantsSaverSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductIndexerInterface $productIndexer,
        ProductIndexerInterface $productModelIndexer,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $this->beConstructedWith(
            $productModelRepository,
            $pqbFactory,
            $productIndexer,
            $productModelIndexer,
            $computeAndPersistProductCompletenesses,
            100
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelDescendantsSaver::class);
    }

    function it_computes_completeness_and_indexes_a_product_model_descendants_which_are_products_and_sub_product_models(
        $productModelRepository,
        $pqbFactory,
        $productIndexer,
        $productModelIndexer,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        ProductQueryBuilderInterface $pqb,
        ProductModelInterface $productModel,
        ProductModelInterface $productModelsChildren1,
        ProductModelInterface $productModelsChildren2,
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

        $variantProduct1->getIdentifier()->willReturn('product_1');
        $variantProduct2->getIdentifier()->willReturn('product_2');
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(['product_1', 'product_2'])->shouldBeCalled();

        $productIndexer->indexFromProductIdentifiers(['product_1', 'product_2'], ['index_refresh' => Refresh::disable()])->shouldBeCalled();

        $productModelRepository->findChildrenProductModels($productModel)->willReturn(
            [$productModelsChildren1, $productModelsChildren2]
        );
        $productModelsChildren1->getCode()->willReturn('foo');
        $productModelsChildren2->getCode()->willReturn('bar');
        $productModelIndexer->indexFromProductIdentifiers(['foo', 'bar'], ['index_refresh' => Refresh::disable()])->shouldBeCalled();

        $productModelIndexer->indexFromProductIdentifier('product_model_code')->shouldBeCalled();

        $this->save($productModel);
    }

    function it_does_not_fail_when_product_model_has_no_child(
        $productModelRepository,
        $pqbFactory,
        $productIndexer,
        $productModelIndexer,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
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

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $productIndexer->indexFromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();

        $productModelRepository->findChildrenProductModels($productModel)->willReturn([]);
        $productModelIndexer->indexFromProductIdentifiers(Argument::cetera())->shouldNotBeCalled();

        $productModelIndexer->indexFromProductIdentifier('product_model_code')->shouldBeCalled();

        $this->save($productModel);
    }

    function it_throws_when_we_dont_save_a_product_model(
        \stdClass $wrongObject
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('save', [$wrongObject]);
    }
}
