<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job\ProjectsRecalculationLauncher;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query\GetProjectCode;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

class ProjectRecalculationLauncherIntegration extends TeamworkAssistantTestCase
{
    public function testRecalculationLauncher()
    {
        $this->createProject('my_project_01', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['usb_keys'],
            ],
        ]);

        $this->createProject('my_project_02', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['tshirt'],
            ],
        ]);

        $launcher = new ProjectsRecalculationLauncher(
            $this->get(GetProjectCode::class),
            'project_calculation',
            $this->get('akeneo_batch.launcher.simple_job_launcher'),
            $this->get('akeneo_batch.job.job_instance_repository'),
            $this->get('logger'),
        );
        $launcher->launch();
    }

    public function testCannotLaunchUnknownProject()
    {
        $this->createProject('my_project_01', 'Julia', 'en_US', 'ecommerce', [
            [
                'field' => 'family',
                'operator' => 'IN',
                'value' => ['usb_keys'],
            ],
        ]);

        $allProjectsCodes = $this->createMock(GetProjectCode::class);
        $allProjectsCodes->method('fetchAll')->willReturn(['my-project-01-ecommerce-en-us', 'unknown_project']);

        $launcher = new ProjectsRecalculationLauncher(
            $allProjectsCodes,
            'project_calculation',
            $this->get('akeneo_batch.launcher.simple_job_launcher'),
            $this->get('akeneo_batch.job.job_instance_repository'),
            $this->get('logger'),
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches("/The project unknown_project doesn't exist/");
        $launcher->launch();
    }
}
