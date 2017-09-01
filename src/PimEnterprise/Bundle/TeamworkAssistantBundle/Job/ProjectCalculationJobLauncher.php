<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

/**
 * Launch the project calculation job for the a project and an user.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationJobLauncher
{
    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /** @var string */
    protected $projectCalculationJobName;

    /**
     * @param JobLauncherInterface  $simpleJobLauncher
     * @param JobInstanceRepository $jobInstanceRepository
     * @param string                $projectCalculationJobName
     */
    public function __construct(
        JobLauncherInterface $simpleJobLauncher,
        JobInstanceRepository $jobInstanceRepository,
        $projectCalculationJobName
    ) {
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->projectCalculationJobName = $projectCalculationJobName;
    }

    /**
     * @param ProjectInterface $project
     */
    public function launch(ProjectInterface $project)
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->projectCalculationJobName);
        if (null === $jobInstance) {
            throw new \RuntimeException('Cannot run project calculation, there is no available job');
        }

        $configuration = [
            'project_code' => $project->getCode(),
            'user_to_notify' => $project->getOwner()->getUsername()
        ];

        $this->simpleJobLauncher->launch($jobInstance, $project->getOwner(), $configuration);
    }
}
