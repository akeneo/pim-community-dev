<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Service\User;

use Akeneo\Connectivity\Connection\Application\Settings\Service\RegenerateUserPasswordInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\UserId;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Driver\Connection as DbalConnection;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegenerateUserPassword implements RegenerateUserPasswordInterface
{
    public function __construct(private UserManager $userManager, private DbalConnection $dbalConnection)
    {
    }

    public function execute(UserId $userId): string
    {
        $user = $this->findUser($userId);
        $password = $this->generatePassword();
        $user->setPlainPassword($password);

        $this->userManager->updateUser($user);
        $this->deleteApiToken($userId);

        return $password;
    }

    private function findUser(UserId $userId): UserInterface
    {
        /** @var ?UserInterface */
        $user = $this->userManager->findUserBy(['id' => $userId->id()]);
        if (null === $user) {
            throw new \InvalidArgumentException(
                \sprintf('User with id "%s" not found.', $userId->id())
            );
        }

        return $user;
    }

    private function generatePassword(): string
    {
        return \str_shuffle(\ucfirst(\substr(\uniqid(), 0, 9)));
    }

    private function deleteApiToken(UserId $userId): void
    {
        $deleteSqlAccessToken = <<<SQL
DELETE FROM pim_api_access_token WHERE user = :user_id
SQL;
        $stmt = $this->dbalConnection->prepare($deleteSqlAccessToken);
        $stmt->execute(['user_id' => $userId->id()]);

        $deleteSqlRefreshToken = <<<SQL
DELETE FROM pim_api_refresh_token WHERE user = :user_id
SQL;
        $stmt = $this->dbalConnection->prepare($deleteSqlRefreshToken);
        $stmt->execute(['user_id' => $userId->id()]);
    }
}
