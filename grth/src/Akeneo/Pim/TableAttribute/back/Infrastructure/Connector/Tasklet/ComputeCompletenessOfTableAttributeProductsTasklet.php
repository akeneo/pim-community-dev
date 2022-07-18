<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Product\ComputeAndPersistProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductUuidsQuery;
use Akeneo\Pim\Enrichment\Product\API\Query\ProductUuidCursorInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert;

class ComputeCompletenessOfTableAttributeProductsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const BATCH_SIZE = 1000;
    private StepExecution $stepExecution;

    public function __construct(
        private ComputeAndPersistProductCompletenesses $computeAndPersistProductCompletenesses,
        private JobRepositoryInterface $jobRepository,
        private ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private MessageBusInterface $messageBus
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $attributeCode = $this->stepExecution->getJobParameters()->get('attribute_code');
        $familyCodes = $this->stepExecution->getJobParameters()->get('family_codes');

        $productUuids = $this->getProductUuidsFromTableAttributeCodes($attributeCode, $familyCodes);
        if ($productUuids->count() === 0) {
            return;
        }
        $this->stepExecution->setTotalItems($productUuids->count());
        $productUuidsToCompute = [];
        foreach ($productUuids as $uuid) {
            $productUuidsToCompute[] = $uuid;
            if (count($productUuidsToCompute) >= self::BATCH_SIZE) {
                $this->computeCompleteness($productUuidsToCompute);
                $productUuidsToCompute = [];
            }
        }

        if (count($productUuidsToCompute) > 0) {
            $this->computeCompleteness($productUuidsToCompute);
        }
    }

    public function isTrackable(): bool
    {
        return true;
    }

    /**
     * @param UuidInterface[] $productUuids
     */
    private function computeCompleteness(array $productUuids): void
    {
        $this->computeAndPersistProductCompletenesses->fromProductUuids($productUuids);
        $this->productAndAncestorsIndexer->indexFromProductUuids($productUuids);

        $this->stepExecution->incrementProcessedItems(count($productUuids));
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    private function getProductUuidsFromTableAttributeCodes(string $attributeCode, array $familyCodes): ProductUuidCursorInterface
    {
        $query = [
            'family' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => $familyCodes,
                ],
            ],
        ];
        $query[$attributeCode] = [
            [
                'operator' => Operators::IS_NOT_EMPTY,
                'value' => null,
            ],
        ];
        $envelope = $this->messageBus->dispatch(new GetProductUuidsQuery($query, null));

        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::notNull($handledStamp, 'The bus does not return any result');
        Assert::isInstanceOf($handledStamp, HandledStamp::class);

        return $handledStamp->getResult();
    }
}
