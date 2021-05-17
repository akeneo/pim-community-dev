<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\Query;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Persistence\Query\SqlIsUserGroupAttachedToProject;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

final class SqlIsUserGroupAttachedToProjectIntegration extends TeamworkAssistantTestCase
{
    private SqlIsUserGroupAttachedToProject $sqlIsUserGroupAttachedToProject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sqlIsUserGroupAttachedToProject = $this->get(
            'pimee_teamwork_assistant.query.sql.is_user_user_group_attached_to_project'
        );
    }

    public function test_a_user_group_is_attached_to_a_project(): void
    {
        $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);

        foreach (['Catalog Manager', 'Marketing', 'Technical Clothing', 'Technical High-Tech'] as $userGroupName) {
            $userGroup = $this->get('pim_user.repository.group')->findOneByIdentifier($userGroupName);
            self::assertNotNull($userGroup);
            self::assertTrue($this->sqlIsUserGroupAttachedToProject->forUserGroupId($userGroup->getId()));
        }

        self::assertFalse($this->sqlIsUserGroupAttachedToProject->forUserGroupId(9999));
    }
}
