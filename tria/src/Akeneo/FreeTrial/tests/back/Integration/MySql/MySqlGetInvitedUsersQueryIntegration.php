<?php

declare(strict_types=1);

namespace Akeneo\Test\FreeTrial\Integration\MySql;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MySqlGetInvitedUsersQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_retrieves_all_invited_users_order_by_creation_date()
    {
        $this->deleteInvitedUsers();

        $invitedUsers = $this->get('Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery')->execute();

        $this->assertSame([], $invitedUsers);

        $this->insertInvitedUsers();

        $invitedUsers = $this->get('Akeneo\FreeTrial\Domain\Query\GetInvitedUsersQuery')->execute();

        $this->assertCount(2, $invitedUsers);
        $this->assertContainsOnlyInstancesOf(InvitedUser::class, $invitedUsers);
        $this->assertSame('test2@test.com', $invitedUsers[0]->getEmail());
        $this->assertEquals(InvitedUserStatus::active(), $invitedUsers[0]->getStatus());
        $this->assertSame('test1@test.com', $invitedUsers[1]->getEmail());
        $this->assertEquals(InvitedUserStatus::invited(), $invitedUsers[1]->getStatus());
    }

    private function deleteInvitedUsers(): void
    {
        $this->get('database_connection')->executeQuery('DELETE FROM akeneo_free_trial_invited_user');
    }

    private function insertInvitedUsers(): void
    {
        $this->get('database_connection')->executeQuery('
INSERT INTO akeneo_free_trial_invited_user (email, status, created_at) VALUES 
(:email1, :status1, :created_at1),
(:email2, :status2, :created_at2)',
            [
                'email1' => 'test1@test.com',
                'status1' => 'invited',
                'created_at1' => '2021-06-16 16:00:00',
                'email2' => 'test2@test.com',
                'status2' => 'active',
                'created_at2' => '2021-06-16 16:05:00',
            ]);
    }
}