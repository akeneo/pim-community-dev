<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\tests\integration\Project;

use PimEnterprise\Bundle\TeamWorkAssistantBundle\tests\integration\TeamWorkAssistantTestCase;

class CreateProjectIntegration extends TeamWorkAssistantTestCase
{
    public function testCreateProjectGenerateCodeAndAddDatagridView()
    {
        $projectRepository = $this->get('pimee_team_work_assistant.repository.project');
        $this->createProject('High-Tech project /42', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);
        $expectedCode = 'high-tech-project-42-tablet-en-us';
        $project = $projectRepository->findOneByIdentifier($expectedCode);
        $this->assertTrue(
            null !== $project,
            sprintf(
                'Project code not well generated. Expected: "%s" but no project found for this code.',
                $expectedCode
            )
        );

        $datagridView = $project->getDatagridView();
        $this->assertTrue(
            null !== $datagridView,
            'Expected to find a new datagrid view.'
        );
        $this->assertTrue(
            $datagridView->getLabel() === $expectedCode,
            sprintf(
                'The new datagrid view must have the label "%s". "%s" found.',
                $expectedCode,
                $datagridView->getLabel()
            )
        );
        $this->assertTrue(
            'project' === $datagridView->getType(),
            sprintf('The new datagrid view must have the type "project". "%s" found.', $datagridView->getType())
        );
    }
}
