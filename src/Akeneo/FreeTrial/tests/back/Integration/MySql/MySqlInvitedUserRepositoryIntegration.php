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
final class MySqlInvitedUserRepositoryIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_saves_an_invited_user()
    {
        $invitedUser = new InvitedUser('test_save1@test.com', InvitedUserStatus::invited());

        $this->get('Akeneo\FreeTrial\Domain\Repository\InvitedUserRepository')->save($invitedUser);

        $invitedUserTest = $this->findInvitedUserByEmail($invitedUser->getEmail());

        $this->assertSame('test_save1@test.com', $invitedUserTest['email']);
        $this->assertSame(InvitedUserStatus::INVITED, $invitedUserTest['status']);
    }

    private function findInvitedUserByEmail(string $email): array
    {
        return $this->get('database_connection')->executeQuery('
SELECT email, status 
FROM akeneo_free_trial_invited_user 
WHERE email = :email', ['email' => $email])->fetch(\PDO::FETCH_ASSOC);
    }
}