<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModelValue;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;
use Oro\Bundle\EntityConfigBundle\Tests\Unit\Fixture\DemoEntity;

class FoundEntityConfigRepository extends EntityRepository
{
    protected static $configEntity;
    protected static $configField;

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        if (isset($criteria['fieldName'])) {
            return self::getResultConfigField();
        } else {
            return self::getResultConfigEntity();
        }
    }

    public function findAll()
    {
        return array(self::getResultConfigEntity());
    }

    public static function getResultConfigEntity()
    {
        if (!self::$configEntity) {
            self::$configEntity = new EntityConfigModel(DemoEntity::ENTITY_NAME);

            self::$configEntity->addField(self::getResultConfigField());

            $configValue = new ConfigModelValue(
                'test_value',
                'test',
                'test_value_origin'
            );

            $configValueSerializable = new ConfigModelValue(
                'test_value_serializable',
                'test',
                array('test_value' => 'test_value_origin')
            );

            self::$configEntity->addValue($configValue);
            self::$configEntity->addValue($configValueSerializable);
        }

        return self::$configEntity;
    }

    public static function getResultConfigField()
    {
        if (!self::$configField) {
            self::$configField = new FieldConfigModel('test', 'string');

            $configValue = new ConfigModelValue(
                'test_value',
                'test',
                'test_value_origin'
            );

            self::$configField->addValue($configValue);
        }

        return self::$configField;
    }
}
