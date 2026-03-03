<?php
declare(strict_types=1);

namespace AkeneoTest\UserManagement\Integration\Bundle\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Storage\Query\GetUserCountInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlGetUserCountIntegration extends TestCase
{
    private GetUserCountInterface $getUserCount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->getUserCount = $this->get('pim_user.query.get_user_count');
    }

    public function testItReturnsTheNumberOfUsersHavingOnlyTheRole()
    {
        self::assertSame(0, $this->getUserCount->forUsersHavingOnlyRole('ROLE_USER'));
        self::assertSame(0, $this->getUserCount->forUsersHavingOnlyRole('ROLE_ADMINISTRATOR'));

        $this->createUser('user1', ['ROLE_USER', 'ROLE_ADMINISTRATOR']);
        self::assertSame(0, $this->getUserCount->forUsersHavingOnlyRole('ROLE_USER'));
        self::assertSame(0, $this->getUserCount->forUsersHavingOnlyRole('ROLE_ADMINISTRATOR'));

        $this->createUser('user2', ['ROLE_USER']);
        $this->createUser('user3', ['ROLE_USER']);
        $this->createUser('user4', ['ROLE_ADMINISTRATOR']);
        self::assertSame(2, $this->getUserCount->forUsersHavingOnlyRole('ROLE_USER'));
        self::assertSame(1, $this->getUserCount->forUsersHavingOnlyRole('ROLE_ADMINISTRATOR'));
    }

    protected function createUser(string $username, array $stringRoles): UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setPlainPassword('admin');
        $user->setEmail($username . '@example.com');
        $user->setSalt('E1F53135E559C253');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        $this->get('pim_user.manager')->updatePassword($user);

        foreach ($stringRoles as $stringRole) {
            $adminRole = $this->get('pim_user.repository.role')->findOneByIdentifier($stringRole);
            self::assertNotNull($adminRole);
            $user->addRole($adminRole);
        }

        if (!in_array(User::ROLE_DEFAULT, $stringRoles)) {
            $userRole = $this->get('pim_user.repository.role')->findOneByIdentifier(User::ROLE_DEFAULT);
            if (null !== $userRole) {
                $user->removeRole($userRole);
            }
        }

        $group = $this->get('pim_user.repository.group')->findOneByIdentifier('IT support');
        if (null !== $group) {
            $user->addGroup($group);
        }

        $violations = $this->get('validator')->validate($user);
        self::assertCount(0, $violations, sprintf('Errors: %s', join(',', array_map(
            fn (ConstraintViolationInterface $violation): string => $violation->getMessage(),
            iterator_to_array($violations)
        ))));
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
