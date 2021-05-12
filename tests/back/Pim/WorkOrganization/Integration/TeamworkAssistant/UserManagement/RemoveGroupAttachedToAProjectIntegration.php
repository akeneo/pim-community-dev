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

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\UserManagement;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\TeamworkAssistant\TeamworkAssistantTestCase;

final class RemoveGroupAttachedToAProjectIntegration extends TeamworkAssistantTestCase
{
    private RemoverInterface $userGroupRemover;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userGroupRemover = $this->get('pim_user.remover.user_group');

        $this->createProject('High-Tech project', 'admin', 'en_US', 'tablet', [
            [
                'field'    => 'categories',
                'operator' => 'IN',
                'value'    => ['high_tech'],
            ],
        ]);
    }

    public function test_it_cannot_be_possible_to_remove_a_group_attached_to_a_project(): void
    {
        self::expectException(\Exception::class);

        $userGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('Catalog Manager');
        self::assertNotNull($userGroup);
        $this->userGroupRemover->remove($userGroup);
    }

    public function test_it_is_possible_to_remove_a_group_non_attached_to_a_project(): void
    {
        $userGroup = $this->get('pim_user.repository.group')->findOneByIdentifier('Read Only');
        self::assertNotNull($userGroup);
        $this->userGroupRemover->remove($userGroup);
    }
}
