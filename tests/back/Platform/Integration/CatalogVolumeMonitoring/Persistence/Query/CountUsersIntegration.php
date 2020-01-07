<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountUsersIntegration extends QueryTestCase
{
    public function testCountOfUsers()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_users');
        $this->createUsers(4, User::TYPE_USER);
        $this->createUsers(2, User::TYPE_API);

        $volume = $query->fetch();

        Assert::assertEquals(4, $volume->getVolume());
        Assert::assertEquals('count_users', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfUsers
     * @param string $type
     */
    protected function createUsers(int $numberOfUsers, string $type)
    {
        $i = 0;
        while ($i < $numberOfUsers) {
            $this->createUser([
                'username'  => 'new_user_' . rand(),
                'email'     => 'test_' . rand().'@test.fr',
                'first_name' => 'firstname_' . rand(),
                'last_name' => 'lastname_' . rand(),
                'password' => rand(),
                'catalog_default_locale' => 'en_US',
                'user_default_locale' => 'en_US',
                'roles' => ['ROLE_USER'],
            ], $type);
            $i++;
        }
    }


    /**
     * @param array $data
     * @param string $type
     * @return UserInterface
     */
    protected function createUser(array $data = [], string $type) : UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, $data, []);

        if ($type === User::TYPE_API) {
            $user->defineAsApiUser();
        }

        $validation = $this->get('validator')->validate($user);
        Assert::assertEquals(0, $validation->count());
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
