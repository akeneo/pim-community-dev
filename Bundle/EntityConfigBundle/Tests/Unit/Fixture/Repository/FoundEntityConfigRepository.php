<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModelValue;
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
            self::$configEntity = new EntityConfigModel(ConfigManagerTest::DEMO_ENTITY);

            $configField = new FieldConfigModel('test', 'string');
            self::$configEntity->addField($configField);

            $configValue             = new ConfigModelValue('test_value', 'test', 'test_value_origin');
            $configValueSerializable = new ConfigModelValue('test_value_serializable', 'test', array('test_value' => 'test_value_origin'));
            self::$configEntity->addValue($configValue);
            self::$configEntity->addValue($configValueSerializable);
        }

        return self::$configEntity;
    }
}
