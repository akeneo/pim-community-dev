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

use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

class CreateProjectIntegration extends TeamworkAssistantTestCase
{
    /**
     * @critical
     */
    public function testCreateProjectGenerateCodeAndAddDatagridView()
    {
        $projectRepository = $this->get('pimee_teamwork_assistant.repository.project');
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

    public function testThatWeCannotCreateAProjectWithLocaleThatDoNotBelongToTheChannel()
    {
        $project = $this->get('pimee_teamwork_assistant.factory.project')->create([
            'label'           => 'High-Tech project /42',
            'locale'          => 'iu_Latn_CA',
            'owner'           => 'admin',
            'channel'         => 'ecommerce',
            'due_date'        => '2120-01-19',
            'datagrid_view'   => ['filters' => '', 'columns' => 'sku,label,family'],
            'product_filters' => [
                [
                    'field'    => 'categories',
                    'operator' => 'IN',
                    'value'    => ['high_tech'],
                ],
            ]
        ]);

        $violation = $this->get('validator')->validate($project);

        $this->assertCount(1, $violation, 'The project locale is not valid');
    }

    public function testThatWeCannotCreateAProjectWithDueDateInThePast()
    {
        $project = $this->get('pimee_teamwork_assistant.factory.project')->create([
            'label'           => 'High-Tech project /42',
            'locale'          => 'en_US',
            'owner'           => 'admin',
            'channel'         => 'ecommerce',
            'due_date'        => '2000-01-19',
            'datagrid_view'   => ['filters' => '', 'columns' => 'sku,label,family'],
            'product_filters' => [
                [
                    'field'    => 'categories',
                    'operator' => 'IN',
                    'value'    => ['high_tech'],
                ],
            ]
        ]);

        $violation = $this->get('validator')->validate($project);

        $this->assertCount(1, $violation, 'The project due date is in the past');
    }

    public function testThatWeCannotCreateAProjectWithEmptyFields()
    {
        $project = $this->get('pimee_teamwork_assistant.factory.project')->create([
            'label'           => null,
            'locale'          => 'en_US',
            'owner'           => 'admin',
            'channel'         => 'ecommerce',
            'due_date'        => '2200-01-19',
            'datagrid_view'   => null,
            'product_filters' => []
        ]);

        $violation = $this->get('validator')->validate($project);

        $this->assertCount(2, $violation, 'The project due date is in the past');
    }
}
