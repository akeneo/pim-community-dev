<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProductRepositoryInterface;

/**
 * Compute all CalculationStep linked to the project to complete it and save it.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCalculationTasklet implements TaskletInterface
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var CalculationStepInterface */
    protected $calculationStep;

    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var SaverInterface */
    protected $projectSaver;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param ProductRepositoryInterface            $productRepository
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param CalculationStepInterface              $calculationStep
     * @param PreProcessingRepositoryInterface      $preProcessingRepository
     * @param SaverInterface                        $projectSaver
     * @param ObjectDetacherInterface               $objectDetacher
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $calculationStep,
        PreProcessingRepositoryInterface $preProcessingRepository,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->productRepository = $productRepository;
        $this->projectRepository = $projectRepository;
        $this->calculationStep = $calculationStep;
        $this->preProcessingRepository = $preProcessingRepository;
        $this->projectSaver = $projectSaver;
        $this->objectDetacher = $objectDetacher;
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

        foreach ($products as $product) {
            $this->calculationStep->execute($product, $project);
            $this->objectDetacher->detach($product);
        }

        $this->projectSaver->save($project);
    }
}
