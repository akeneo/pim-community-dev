<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\Repository;

use Doctrine\ORM\EntityRepository;

class NotFoundEntityConfigRepository extends EntityRepository
{
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return null;
    }
}
