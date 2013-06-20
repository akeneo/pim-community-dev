<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Unit\Fixture\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;

class UserRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $user = new User;
        $user->setUsername('testUser');

        return $user;
    }
}
