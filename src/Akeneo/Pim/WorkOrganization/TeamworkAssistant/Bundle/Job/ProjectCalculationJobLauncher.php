<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;

/**
 * Launch the project calculation job for the a project and an user.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCalculationJobLauncher
{
    public function __construct(
        private JobLauncherInterface $simpleJobLauncher,
        private JobInstanceRepository $jobInstanceRepository,
        private string $projectCalculationJobName
    ) {
    }

    public function launch(ProjectInterface $project): void
    {
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->projectCalculationJobName);
        if (null === $jobInstance) {
            throw new \RuntimeException('Cannot run project calculation, there is no available job');
        }

        $configuration = [
            'project_code' => $project->getCode(),
            'users_to_notify' => [$project->getOwner()->getUserIdentifier()]
        ];

        $this->simpleJobLauncher->launch($jobInstance, $project->getOwner(), $configuration);
    }
}
