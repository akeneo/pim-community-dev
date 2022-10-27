<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query\GetProjectCode;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Psr\Log\LoggerInterface;

/**
 * Run project calculations for all enrichment projects.
 * Recalculate all enrichment projects (Warning: Be aware it can be very time-consuming)
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class ProjectsRecalculationLauncher
{
    public function __construct(
        private GetProjectCode $allProjectCodes,
        private string $projectCalculationJobName,
        private JobLauncherInterface $jobLauncher,
        private JobInstanceRepository $jobInstanceRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function launch(): void
    {
        $this->logger->info('Start TWA projects recalculation');

        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier('akeneo:batch:job');

        foreach ($this->allProjectCodes->fetchAll() as $projectCode) {
            $config = [
                'code' => $this->projectCalculationJobName,
                '-c' => sprintf('{"project_code":"%s"}', $projectCode),
            ];
            $this->jobLauncher->launch($jobInstance, null, $config);
        }
        $this->logger->info('End TWA projects recalculation');
    }
}
