<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeCompletenessOfFamilyProductsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
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
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        StepExecution $stepExecution
    )
    {
        $this->beConstructedWith(
            $productQueryBuilderFactory,
            $familyReader,
            $cacheClearer,
            $jobRepository,
            $computeAndPersistProductCompletenesses
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ComputeCompletenessOfFamilyProductsTasklet::class);
    }

    function it_does_nothing_if_there_is_no_family(
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses
    ) {
        $familyReader->read()->shouldBeCalledOnce()->willReturn(null);
        $productQueryBuilderFactory->create()->shouldNotBeCalled();
        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::any())->shouldNotBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_products_of_family(
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes,
        FamilyInterface $familyTshirt
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $familyReader->read()->shouldBeCalledTimes(3)->willReturn($familyShoes, $familyTshirt, null);
        $familyShoes->getCode()->willReturn('Shoes');
        $familyTshirt->getCode()->willReturn('Tshirt');

        $productQueryBuilder->addFilter('family', 'IN', ['Shoes', 'Tshirt'])->shouldBeCalled();
        $productQueryBuilder->execute()->shouldBeCalled()->willReturn(
            new ProductCursor(['product_shoes_1', 'product_shoes_2', 'product_tshirt_1', 'product_tshirt_2']),
        );

        $computeAndPersistProductCompletenesses->fromProductIdentifiers([
            'product_shoes_1',
            'product_shoes_2',
            'product_tshirt_1',
            'product_tshirt_2',
        ])->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('process', 4)->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_more_than_1000_products(
        ProductQueryBuilderInterface $productQueryBuilder,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        ItemReaderInterface $familyReader,
        ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes
    ) {
        $productQueryBuilderFactory->create()->willReturn($productQueryBuilder);

        $familyReader->read()->shouldBeCalledTimes(2)->willReturn($familyShoes, null);
        $familyShoes->getCode()->willReturn('Shoes');

        $productQueryBuilder->addFilter('family', 'IN', ['Shoes'])->shouldBeCalled();
        $productQueryBuilder->execute()->shouldBeCalled()->willReturn(
            new ProductCursor(array_map(function (int $i): string {
                return 'product_' . $i;
            }, range(1, 1006)))
        );

        $computeAndPersistProductCompletenesses->fromProductIdentifiers(Argument::type('array'))->shouldBeCalledTimes(2);

        $stepExecution->incrementSummaryInfo('process', 1000)->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('process', 6)->shouldBeCalled();
        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(2);

        $this->execute();
    }
}

class ProductCursor extends \ArrayIterator implements CursorInterface
{
}
