<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Job;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\ActivityManagerBundle\Doctrine\ORM\Repository\JobInstanceRepository;
use PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\ProjectCalculationJobLauncherInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Launch the project calculation job for the a project and an user.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationJobLauncher implements ProjectCalculationJobLauncherInterface
{
    /** @var JobLauncherInterface */
    protected $simpleJobLauncher;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepository;

    /**
     * @param JobLauncherInterface  $simpleJobLauncher
     * @param JobInstanceRepository $jobInstanceRepository
     */
    public function __construct(JobLauncherInterface $simpleJobLauncher, JobInstanceRepository $jobInstanceRepository)
    {
        $this->simpleJobLauncher = $simpleJobLauncher;
        $this->jobInstanceRepository = $jobInstanceRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(UserInterface $user, ProjectInterface $project)
    {
        $jobInstance = $this->jobInstanceRepository->getProjectCalculation();

        $this->simpleJobLauncher->launch($jobInstance, $user, ['project_code' => $project->getCode()]);
    }
}
