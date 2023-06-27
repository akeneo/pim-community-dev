<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert;

/**
 *  Computation of the completeness for all products belonging to a family that has been updated by mass action
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO refactor with Akeneo\Pim\Enrichment\Component\Product\Job\ComputeCompletenessOfProductsFamilyTasklet
 *            that work only for unitary update
 */
class ComputeCompletenessOfFamilyProductsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 100;

    private StepExecution $stepExecution;

    public function __construct(
        private ItemReaderInterface $familyReader,
        private EntityManagerClearerInterface $cacheClearer,
        private JobRepositoryInterface $jobRepository,
        private CompletenessCalculator $completenessCalculator,
        private SaveProductCompletenesses $saveProductCompletenesses,
        private MessageBusInterface $messageBus
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->familyReader instanceof InitializableInterface) {
            $this->familyReader->initialize();
        }

        $familyCodes = $this->extractFamilyCodes();
        if (empty($familyCodes)) {
            return;
        }

        $productUuids = $this->getProductUuidsForFamilies($familyCodes);
        $this->stepExecution->setTotalItems($productUuids->count());

        $productUuidsToCompute = [];
        foreach ($productUuids as $uuid) {
            $productUuidsToCompute[] = $uuid;

            if (count($productUuidsToCompute) >= self::BATCH_SIZE) {
                $this->computeCompleteness($productUuidsToCompute);
                $this->cacheClearer->clear();
                $productUuidsToCompute = [];
            }
        }

        if (count($productUuidsToCompute) > 0) {
            $this->computeCompleteness($productUuidsToCompute);
        }
    }

    private function computeCompleteness(array $productUuids): void
    {
        $completenessCollections = $this->completenessCalculator->fromProductUuids($productUuids);
        $this->saveProductCompletenesses->saveAll($completenessCollections);

        $this->stepExecution->incrementProcessedItems(count($productUuids));
        $this->stepExecution->incrementSummaryInfo('process', count($productUuids));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function extractFamilyCodes()
    {
        $familyCodes = [];

        while (true) {
            $family = $this->familyReader->read();
            if (null === $family) {
                break;
            }

            Assert::isInstanceOf($family, FamilyInterface::class);

            $familyCodes[] = $family->getCode();
        }

        return $familyCodes;
    }

    private function getProductUuidsForFamilies(array $familyCodes): ProductUuidCursorInterface
    {
        $envelope = $this->messageBus->dispatch(new GetProductUuidsQuery([
            'family' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => $familyCodes,
                ],
            ]
        ], null));

        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::notNull($handledStamp, 'The bus does not return any result');

        return $handledStamp->getResult();
    }
}
