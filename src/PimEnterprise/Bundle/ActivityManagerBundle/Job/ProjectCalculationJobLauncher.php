<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Job;

use Akeneo\ActivityManager\Bundle\Doctrine\Repository\JobInstanceRepository;
use Akeneo\ActivityManager\Component\Job\ProjectCalculation\ProjectCalculationJobLauncherInterface;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Launch the project calculation job for the a project and an user.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationJobLauncher implements ProjectCalculationJobLauncherInterface
{
    /** @var JobLauncherInterface */
    private $simpleJobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /**
     * Launch the job.
     *
     * @param JobLauncherInterface  $simpleJobLauncher
     * @param JobInstanceRepository $jobInstanceRepository
     */
    public function __construct(JobLauncherInterface $simpleJobLauncher, JobInstanceRepository $jobInstanceRepository)
    {
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * @param UserInterface    $user
     * @param ProjectInterface $project
     */
    public function launch(UserInterface $user, ProjectInterface $project)
    {
        $jobInstance = $this->jobInstanceRepository->getProjectCalculation();

        $this->simpleJobLauncher->launch($jobInstance, $user, ['project_id' => $project->getId()]);
    }
}
