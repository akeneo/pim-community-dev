<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Item\TrackableTaskletInterface;
use Akeneo\Tool\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * Compute all CalculationStep linked to the project to complete it and save it.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCalculationTasklet implements TaskletInterface, TrackableTaskletInterface
{
    private const PRODUCT_BATCH_SIZE = 1000;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var CalculationStepInterface */
    protected $calculationStep;

    /** @var SaverInterface */
    protected $projectSaver;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var EntityManagerClearerInterface */
    protected $cacheClearer;

    private JobRepositoryInterface $jobRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $calculationStep,
        SaverInterface $projectSaver,
        EntityManagerClearerInterface $cacheClearer,
        JobRepositoryInterface $jobRepository
    ) {
        $this->productRepository = $productRepository;
        $this->projectRepository = $projectRepository;
        $this->calculationStep = $calculationStep;
        $this->projectSaver = $projectSaver;
        $this->cacheClearer = $cacheClearer;
        $this->jobRepository = $jobRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $project = $this->project();
        $products = $this->productRepository->findByProject($project);

        $handledProductsCount = 0;
        foreach ($products as $product) {
            $this->calculationStep->execute($product, $project);
            $this->stepExecution->incrementSummaryInfo('processed_products');
            $handledProductsCount++;

            if (self::PRODUCT_BATCH_SIZE === $handledProductsCount) {
                $this->projectSaver->save($project);
                $this->updateProgress(self::PRODUCT_BATCH_SIZE);

                $this->cacheClearer->clear();
                $project = $this->project();
                $handledProductsCount = 0;
            }
        }

        if ($handledProductsCount > 0) {
            $this->projectSaver->save($project);
            $this->updateProgress($handledProductsCount);
        }
    }

    public function totalItems(): int
    {
        $project = $this->project();
        if (null === $project) {
            return 0;
        }

        $products = $this->productRepository->findByProject($project);

        return $products->count();
    }

    private function project(): ?ProjectInterface
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $projectCode = $jobParameters->get('project_code');
        $project = $this->projectRepository->findOneByIdentifier($projectCode);

        return $project;
    }

    protected function updateProgress(int $totalProductsCount): void
    {
        $this->stepExecution->incrementProcessedItems($totalProductsCount);
        $this->jobRepository->updateStepExecution($this->stepExecution);
    }
}
