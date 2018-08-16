<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Job;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * This StepExecution retrieves children of the given product model in order to:
 * - Calculate their completeness if they are variant product
 * - Reindex them in Elasticsearch
*
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ComputeProductModelsDescendantsTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var SaverInterface */
    private $productModelDescendantsSaver;

    /** @var EntityManagerClearerInterface */
    private $cacheClearer;

    /**
     * @param ProductModelRepositoryInterface $productModelRepository
     * @param SaverInterface                  $productModelDescendantsSaver
     * @param EntityManagerClearerInterface   $cacheClearer
     */
    public function __construct(
        ProductModelRepositoryInterface $productModelRepository,
        SaverInterface $productModelDescendantsSaver,
        EntityManagerClearerInterface $cacheClearer
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->productModelDescendantsSaver = $productModelDescendantsSaver;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     *
     * As we used cache clearer on product model descendants saver,
     * We should hydrate models one per one and we don't need to detach entities anymore
     */
    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $productModelCodes = $jobParameters->get('product_model_codes');

        foreach ($productModelCodes as $productModelCode) {
            $productModel = $this->productModelRepository->findOneByIdentifier($productModelCode);
            $this->productModelDescendantsSaver->save($productModel);
            $this->cacheClearer->clear();
        }
    }
}
