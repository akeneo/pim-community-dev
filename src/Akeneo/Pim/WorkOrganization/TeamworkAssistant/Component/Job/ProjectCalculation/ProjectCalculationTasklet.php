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
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * Compute all CalculationStep linked to the project to complete it and save it.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCalculationTasklet implements TaskletInterface
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

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var EntityManagerClearerInterface|null */
    protected $cacheClearer;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $calculationStep,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher,
        // @todo merge on master: remove null and remove $objectDetacher
        EntityManagerClearerInterface $cacheClearer = null
    ) {
        $this->productRepository = $productRepository;
        $this->projectRepository = $projectRepository;
        $this->calculationStep = $calculationStep;
        $this->projectSaver = $projectSaver;
        $this->objectDetacher = $objectDetacher;
        $this->cacheClearer = $cacheClearer;
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
        $jobParameters = $this->stepExecution->getJobParameters();
        $projectCode = $jobParameters->get('project_code');
        $project = $this->projectRepository->findOneByIdentifier($projectCode);
        $products = $this->productRepository->findByProject($project);

        $handledProductsCount = 0;
        foreach ($products as $product) {
            $this->calculationStep->execute($product, $project);
            $this->objectDetacher->detach($product);
            $this->stepExecution->incrementSummaryInfo('processed_products');

            if (self::PRODUCT_BATCH_SIZE <= ++$handledProductsCount && $this->cacheClearer) {
                $this->projectSaver->save($project);
                $this->cacheClearer->clear();
                $project = $this->projectRepository->findOneByIdentifier($projectCode);
                $handledProductsCount = 0;
            }
        }

        if ($handledProductsCount > 0) {
            $this->projectSaver->save($project);
        }
    }
}
