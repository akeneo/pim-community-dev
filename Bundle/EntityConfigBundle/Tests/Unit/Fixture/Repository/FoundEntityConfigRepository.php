<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityConfigBundle\Entity\ConfigEntity;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigField;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;

class FoundEntityConfigRepository extends EntityRepository
{
    protected static $configEntity;

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return self::getResultConfigEntity();
    }

    public static function getResultConfigEntity()
    {
        if (!self::$configEntity) {
            self::$configEntity = new ConfigEntity(ConfigManagerTest::DEMO_ENTITY);
            $configField  = new ConfigField('test', 'string');
            self::$configEntity->addField($configField);
        }

        return self::$configEntity;
    }
}
