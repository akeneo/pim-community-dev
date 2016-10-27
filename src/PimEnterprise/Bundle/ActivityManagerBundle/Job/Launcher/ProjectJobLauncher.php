<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Bundle\Job\Launcher;

use Akeneo\ActivityManager\Component\Job\Launcher\ProjectLauncherInterface;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectJobLauncher implements ProjectLauncherInterface
{
    /** @var JobLauncherInterface */
    private $simpleJobLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

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
     * @param UserInterface    $user
     * @param ProjectInterface $project
     */
    public function launch(UserInterface $user, ProjectInterface $project)
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('project_calculation');

        $filters = json_decode($project->getProductFilters());
        $projectId = $project->getId();
        $configuration = ['filters' => $filters, 'project_id' => $projectId];

        $this->simpleJobLauncher->launch($jobInstance, $user, $configuration);
    }
}
