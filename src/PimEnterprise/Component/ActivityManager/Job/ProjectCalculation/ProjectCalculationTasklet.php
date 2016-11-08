<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Job\ProjectCalculation;

use Akeneo\ActivityManager\Component\Repository\ProductRepositoryInterface;
use Akeneo\ActivityManager\Component\Repository\ProjectRepositoryInterface;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\CalculationStep\CalculationStepInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Pim\Component\Connector\Step\TaskletInterface;

/**
 * Compute all CalculationStep linked to the project to complete it and save it.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectCalculationTasklet implements TaskletInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var CalculationStepInterface */
    private $calculationStep;

    /** @var SaverInterface */
    private $projectSaver;

    /** @var ObjectDetacherInterface */
    private $objectDetacher;

    /** @var StepExecution */
    private $stepExecution;

    /**
     * @param ProductRepositoryInterface            $productRepository
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param CalculationStepInterface              $calculationStep
     * @param SaverInterface                        $projectSaver
     * @param ObjectDetacherInterface               $objectDetacher
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $projectRepository,
        CalculationStepInterface $calculationStep,
        SaverInterface $projectSaver,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->productRepository = $productRepository;
        $this->projectRepository = $projectRepository;
        $this->calculationStep = $calculationStep;
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

        $project = $this->projectRepository->findOneByIdentifier($jobParameters->get('project_id'));
        $products = $this->productRepository->findByProject($project);

        foreach ($products as $product) {
            $this->calculationStep->execute($product, $project);
            $this->objectDetacher->detach($product);
        }

        $this->projectSaver->save($project);
    }
}
