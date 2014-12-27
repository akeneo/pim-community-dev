<?php

namespace Akeneo\Bundle\StorageUtilsBundle\MongoDB;

class CustomTypeRegisterer
{
    const ODM_ENTITIES_TYPE = 'entities';
    const ODM_ENTITY_TYPE = 'entity';

    public static function register()
    {
        if (class_exists('\Doctrine\ODM\MongoDB\Types\Type')) {

            \Doctrine\ODM\MongoDB\Types\Type::registerType(
                self::ODM_ENTITIES_TYPE,
                'Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entities'
            );

            \Doctrine\ODM\MongoDB\Types\Type::registerType(
                self::ODM_ENTITY_TYPE,
                'Akeneo\Bundle\StorageUtilsBundle\MongoDB\Type\Entity'
            );
        }
    }
}
