<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Job;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelDescendantsAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
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
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IndexFamilyProductsAndProductModelsTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const DEFAULT_BATCH_SIZE = 100;

    private StepExecution $stepExecution;

    public function __construct(
        private JobRepositoryInterface $jobRepository,
        private ItemReaderInterface $familyReader,
        private ProductQueryBuilderFactoryInterface $productModelQueryBuilderFactory,
        private ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private ProductModelDescendantsAndAncestorsIndexer $productModelDescendantsAndAncestorsIndexer,
        private EntityManagerClearerInterface $cacheClearer,
        private MessageBusInterface $messageBus,
        private int $batchSize = self::DEFAULT_BATCH_SIZE
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

        $familyCodes = $this->getFamilyCodes();
        if (empty($familyCodes)) {
            return;
        }

        $productUuids = $this->getProductUuidsForFamilies($familyCodes);
        $productModels = $this->getProductModelsForFamilies($familyCodes);

        $this->stepExecution->setTotalItems($productUuids->count() + $productModels->count());

        $productUuidsToIndex = [];
        $productModelCodesToIndex = [];

        foreach ($productUuids as $productUuid) {
            $productUuidsToIndex[] = $productUuid;

            if (count($productUuidsToIndex) >= $this->batchSize) {
                $this->indexProducts($productUuidsToIndex);
                $this->cacheClearer->clear();
                $productUuidsToIndex = [];
            }
        }

        if (count($productUuidsToIndex) > 0) {
            $this->indexProducts($productUuidsToIndex);
        }

        foreach ($productModels as $productModel) {
            Assert::isInstanceOf($productModel, ProductModelInterface::class);
            $productModelCodesToIndex[] = $productModel->getCode();

            if (count($productModelCodesToIndex) >= $this->batchSize) {
                $this->indexProductModels($productModelCodesToIndex);
                $this->cacheClearer->clear();
                $productModelCodesToIndex = [];
            }
        }

        if (count($productModelCodesToIndex) > 0) {
            $this->indexProductModels($productModelCodesToIndex);
        }
    }

    /**
     * @return string[]
     */
    private function getFamilyCodes(): array
    {
        $familyCodes = [];
        while (null !== $family = $this->readFamily()) {
            $familyCodes[] = $family->getCode();
        }

        return $familyCodes;
    }

    private function readFamily(): ?FamilyInterface
    {
        $family = $this->familyReader->read();
        Assert::nullOrIsInstanceOf($family, FamilyInterface::class);

        return $family;
    }

    /**
     * @param string[] $familyCodes
     */
    private function getProductUuidsForFamilies(array $familyCodes): ProductUuidCursorInterface
    {
        $envelope = $this->messageBus->dispatch(new GetProductUuidsQuery([
            'family' => [
                [
                    'operator' => Operators::IN_LIST,
                    'value' => $familyCodes,
                ],
            ],
        ], null));

        $handledStamp = $envelope->last(HandledStamp::class);
        Assert::notNull($handledStamp, 'The bus does not return any result');

        return $handledStamp->getResult();
    }

    /**
     * @param string[] $familyCodes
     */
    private function getProductModelsForFamilies(array $familyCodes): CursorInterface
    {
        $pqb = $this->productModelQueryBuilderFactory->create();
        $pqb->addFilter('family', Operators::IN_LIST, $familyCodes);

        return $pqb->execute();
    }

    /**
     * @param UuidInterface[] $productUuids
     */
    private function indexProducts(array $productUuids): void
    {
        $this->productAndAncestorsIndexer->indexFromProductUuids($productUuids);

        $productCount = count($productUuids);

        $this->stepExecution->incrementProcessedItems($productCount);
        $this->stepExecution->incrementSummaryInfo('process', $productCount);
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }

    /**
     * @param string[] $productModelCodes
     */
    private function indexProductModels(array $productModelCodes): void
    {
        $this->productModelDescendantsAndAncestorsIndexer->indexFromProductModelCodes($productModelCodes);

        $productModelCount = count($productModelCodes);

        $this->stepExecution->incrementProcessedItems($productModelCount);
        $this->stepExecution->incrementSummaryInfo('process', $productModelCount);
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }
}
