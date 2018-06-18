<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\QueryTestCase;
use Pim\Bundle\UserBundle\Entity\UserInterface;

class CountUsersIntegration extends QueryTestCase
{
    public function testCountOfUsers()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_users');
        $this->createUsers(8);

        $volume = $query->fetch();

        // one user exist in the minimal catalog
        Assert::assertEquals(9, $volume->getVolume());
        Assert::assertEquals('count_users', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfUsers
     */
    protected function createUsers(int $numberOfUsers)
    {
        $i = 0;
        while ($i < $numberOfUsers) {
            $this->createUser([
                'username'  => 'new_user_' . rand(),
                'email'     => 'test_' . rand().'@test.fr',
                'first_name' => rand(),
                'last_name' => rand(),
                'password' => rand(),
                'catalog_default_locale' => 'en_US',
                'user_default_locale' => 'en_US'
            ]);
            $i++;
        }
    }


    /**
     * @param array $data
     * @return UserInterface
     */
    protected function createUser(array $data = []) : UserInterface
    {
        $user = $this->get('pim_user.factory.user')->create();
        $this->get('pim_user.updater.user')->update($user, $data, []);
        $validation = $this->get('validator')->validate($user);
        Assert::assertEquals(0, $validation->count());
        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
