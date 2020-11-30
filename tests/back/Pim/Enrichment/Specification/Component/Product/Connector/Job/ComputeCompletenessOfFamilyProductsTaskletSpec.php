<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeCompletenessOfFamilyProductsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Reader\File\Csv\Reader;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComputeCompletenessOfFamilyProductsTaskletSpec extends ObjectBehavior
{
    function let(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution
    )
    {
        $this->beConstructedWith(
            $productQueryBuilderFactory,
            $familyReader,
            $cacheClearer,
            $jobRepository,
            $completenessCalculator,
            $saveProductCompletenesses
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfFamilyProductsTasklet::class);
    }

    function it_track_processed_items()
    {
        $this->shouldImplement(TrackableTaskletInterface::class);
        $this->isTrackable()->shouldReturn(true);
    }

    function it_does_nothing_if_there_is_no_family(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $familyReader->read()->shouldBeCalledOnce()->willReturn(null);
        $productQueryBuilderFactory->create()->shouldNotBeCalled();
        $completenessCalculator->fromProductIdentifiers(Argument::any())->shouldNotBeCalled();
        $saveProductCompletenesses->saveAll(Argument::any())->shouldNotBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_products_of_family(
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes,
        FamilyInterface $familyTshirt,
        ProductInterface $product1,
        ProductInterface $product2,
        ProductInterface $product3,
        ProductInterface $product4
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $familyReader->read()->shouldBeCalledTimes(3)->willReturn($familyShoes, $familyTshirt, null);
        $familyShoes->getCode()->willReturn('Shoes');
        $familyTshirt->getCode()->willReturn('Tshirt');

        $product1->getIdentifier()->willReturn('product_shoes_1');
        $product2->getIdentifier()->willReturn('product_shoes_2');
        $product3->getIdentifier()->willReturn('product_tshirt_1');
        $product4->getIdentifier()->willReturn('product_tshirt_2');

        $productQueryBuilder->addFilter('family', 'IN', ['Shoes', 'Tshirt'])->shouldBeCalled();
        $productQueryBuilder->execute()->shouldBeCalled()->willReturn(
            new ProductCursor([$product1->getWrappedObject(), $product2->getWrappedObject(), $product3->getWrappedObject(), $product4->getWrappedObject()]),
        );

        $completenessCalculator->fromProductIdentifiers([
            'product_shoes_1',
            'product_shoes_2',
            'product_tshirt_1',
            'product_tshirt_2',
        ])->shouldBeCalled()->willReturn(['completeness_collection']);
        $saveProductCompletenesses->saveAll(['completeness_collection'])->shouldBeCalled();

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 4)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(4)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_more_than_1000_products(
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $familyReader->read()->shouldBeCalledTimes(2)->willReturn($familyShoes, null);
        $familyShoes->getCode()->willReturn('Shoes');

        $productQueryBuilder->addFilter('family', 'IN', ['Shoes'])->shouldBeCalled();
        $productQueryBuilder->execute()->shouldBeCalled()->willReturn(
            new ProductCursor(array_map(function (int $i): ProductInterface {
                return (new Product())->setIdentifier('product_' . $i);
            }, range(1, 1006)))
        );

        $completenessCalculator->fromProductIdentifiers(Argument::type('array'))->shouldBeCalledTimes(2);
        $saveProductCompletenesses->saveAll(Argument::type('array'))->shouldBeCalledTimes(2);

        $stepExecution->setTotalItems(1006)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 1000)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 6)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(1000)->shouldBeCalledOnce();
        $stepExecution->incrementProcessedItems(6)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(2);

        $this->execute();
    }
}

class ProductCursor extends \ArrayIterator implements CursorInterface
{
}
