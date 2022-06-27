<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Project;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Filter\ProjectCompletenessFilter;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

class FilterByProjectProgressIntegration extends TeamworkAssistantTestCase
{
    public function testFilterOnProjectProgressWithProductModels(): void
    {
        $project = $this->createProject('Hard drives - ecommerce', 'Julia', 'en_US', 'ecommerce', [
            [
                'field'    => 'family',
                'operator' => 'IN',
                'value'    => ['hard_drives'],
            ],
            [
                'field'    => 'name',
                'operator' => 'STARTS WITH',
                'value'    => 'Sandisk',
            ],
        ]);

        $productIdentifiers = $this->get('pimee_teamwork_assistant.repository.project_completeness')
            ->findProductIdentifiers($project, ProjectCompletenessFilter::OWNER_IN_PROGRESS, 'Julia');

        $this->assertEqualsCanonicalizing(
            ['ssd-sandisk-plus-tlc-240', 'ssd-sandisk-plus-tlc-480'],
            $productIdentifiers
        );
    }
}
