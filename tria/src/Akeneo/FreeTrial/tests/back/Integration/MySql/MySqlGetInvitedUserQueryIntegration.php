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

namespace Akeneo\Test\FreeTrial\Integration\MySql;

use Akeneo\FreeTrial\Domain\Model\InvitedUser;
use Akeneo\FreeTrial\Domain\ValueObject\InvitedUserStatus;
use Akeneo\Test\Integration\TestCase;

final class MySqlGetInvitedUserQueryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_retrieves_an_invited_user()
    {
        $this->deleteInvitedUsers();

        $invitedUser = $this->get('Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery')->execute('test1@test.com');
        $this->assertNull($invitedUser);

        $this->insertInvitedUsers();

        $invitedUser = $this->get('Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery')->execute('test1@test.com');
        $this->assertSame('test1@test.com', $invitedUser->getEmail());
        $this->assertEquals(InvitedUserStatus::invited(), $invitedUser->getStatus());

        $invitedUser = $this->get('Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery')->execute('test2@test.com');
        $this->assertNull($invitedUser);

        $invitedUser = $this->get('Akeneo\FreeTrial\Domain\Query\GetInvitedUserQuery')->execute('unknown@test.com');
        $this->assertNull($invitedUser);
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
