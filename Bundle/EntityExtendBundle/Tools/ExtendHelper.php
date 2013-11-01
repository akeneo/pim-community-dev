<?php

namespace Oro\Bundle\EntityExtendBundle\Tools;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;

class ExtendHelper
{
    /**
     * @param $type
     * @return string
     */
    public static function getReversRelationType($type)
    {
        switch ($type) {
            case 'oneToMany':
                return 'manyToOne';
            case 'manyToOne':
                return 'oneToMany';
            case 'manyToMany':
                return 'manyToMany';
            default:
                return $type;
        }
    }

    /**
     * @param FieldConfigId $selfFieldId
     * @param string        $targetEntity
     * @return string
     */
    public static function generateManyToManyJoinTableName(FieldConfigId $selfFieldId, $targetEntity)
    {
        $selfClassArray = explode('\\', $selfFieldId->getClassName());
        $selfClassName  = array_pop($selfClassArray);

        $targetClassArray = explode('\\', $targetEntity);
        $targetClassName  = array_pop($targetClassArray);

        return strtolower('oro_extend_' . $selfClassName . '_' . $targetClassName . '_' . $selfFieldId->getFieldName());
    }
}
