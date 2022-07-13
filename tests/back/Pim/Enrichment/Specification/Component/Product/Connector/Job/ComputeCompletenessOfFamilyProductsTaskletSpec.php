<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\IdentifierResult;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product\SqlFindProductUuids;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Job\ComputeCompletenessOfFamilyProductsTasklet;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Test\Common\FakeCursor;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class ComputeCompletenessOfFamilyProductsTaskletSpec extends ObjectBehavior
{
    function let(
        ItemReaderInterface $familyReader,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        MessageBusInterface $messageBus
    ) {
        $this->beConstructedWith(
            $familyReader,
            $cacheClearer,
            $jobRepository,
            $completenessCalculator,
            $saveProductCompletenesses,
            $messageBus
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
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses
    ) {
        $familyReader->read()->shouldBeCalledOnce()->willReturn(null);
        $completenessCalculator->fromProductUuids(Argument::any())->shouldNotBeCalled();
        $saveProductCompletenesses->saveAll(Argument::any())->shouldNotBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_products_of_family(
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes,
        FamilyInterface $familyTshirt,
        MessageBusInterface $messageBus
    ) {
        $uuids = [Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4(), Uuid::uuid4()];

        $familyReader->read()->shouldBeCalledTimes(3)->willReturn($familyShoes, $familyTshirt, null);
        $familyShoes->getCode()->willReturn('Shoes');
        $familyTshirt->getCode()->willReturn('Tshirt');

        $cursor = new FakeCursor($uuids);

        $messageBus->dispatch(Argument::type(GetProductUuidsQuery::class))->willReturn(
            new Envelope(new \stdClass(), [new HandledStamp($cursor, '')])
        );

        $completenessCalculator->fromProductUuids(Argument::any())->shouldBeCalled()->willReturn(['completeness_collection']);
        $saveProductCompletenesses->saveAll(['completeness_collection'])->shouldBeCalled();

        $stepExecution->setTotalItems(4)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 4)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(4)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalled();

        $this->execute();
    }

    function it_compute_and_persists_the_completeness_of_more_than_1000_products(
        ItemReaderInterface $familyReader,
        CompletenessCalculator $completenessCalculator,
        SaveProductCompletenesses $saveProductCompletenesses,
        StepExecution $stepExecution,
        JobRepositoryInterface $jobRepository,
        FamilyInterface $familyShoes,
        EntityManagerClearerInterface $cacheClearer,
        MessageBusInterface $messageBus
    ) {
        $familyReader->read()->shouldBeCalledTimes(2)->willReturn($familyShoes, null);
        $familyShoes->getCode()->willReturn('Shoes');

        $cursor = new FakeCursor(array_map(fn (): UuidInterface => Uuid::uuid4(), range(1, 1006)));
        $messageBus->dispatch(Argument::type(GetProductUuidsQuery::class))->willReturn(
            new Envelope(new \stdClass(), [new HandledStamp($cursor, '')])
        );

        $completenessCalculator->fromProductUuids(Argument::type('array'))->shouldBeCalledTimes(11);
        $saveProductCompletenesses->saveAll(Argument::type('array'))->shouldBeCalledTimes(11);
        $cacheClearer->clear()->shouldBeCalledTimes(10);

        $stepExecution->setTotalItems(1006)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('process', 100)->shouldBeCalledTimes(10);
        $stepExecution->incrementSummaryInfo('process', 6)->shouldBeCalled();
        $stepExecution->incrementProcessedItems(100)->shouldBeCalledTimes(10);
        $stepExecution->incrementProcessedItems(6)->shouldBeCalledOnce();

        $jobRepository->updateStepExecution($stepExecution)->shouldBeCalledTimes(11);

        $this->execute();
    }
}

class IdentifierResultCursor extends \ArrayIterator implements CursorInterface
{
}
