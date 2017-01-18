<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Component\ActivityManager\Repository\PreProcessingRepositoryInterface;

/**
 * Step executed before a project calculation.
 *
 * It will reset all data we must calculate during the calculation like the user groups impacted by the project
 * unlike some data we only need to be refresh.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class PrepareProjectCalculationTasklet implements TaskletInterface
{
    /** @var PreProcessingRepositoryInterface */
    protected $preProcessingRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * @param PreProcessingRepositoryInterface      $preProcessingRepository
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     */
    public function __construct(
        PreProcessingRepositoryInterface $preProcessingRepository,
        IdentifiableObjectRepositoryInterface $projectRepository
    ) {
        $this->preProcessingRepository = $preProcessingRepository;
        $this->projectRepository = $projectRepository;
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

        $project->resetUserGroups();

        $this->preProcessingRepository->prepareProjectCalculation($project);
    }
}
