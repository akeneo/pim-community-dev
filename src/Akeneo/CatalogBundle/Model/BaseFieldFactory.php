<?php
namespace Akeneo\CatalogBundle\Model;

use Akeneo\CatalogBundle\Entity\ProductEntity as EntityProductEntity;
use Akeneo\CatalogBundle\Entity\ProductType as EntityProductType;
use Akeneo\CatalogBundle\Entity\ProductGroup as EntityProductGroup;
use Akeneo\CatalogBundle\Entity\ProductField as EntityProductField;
use Akeneo\CatalogBundle\Entity\ProductValue as EntityProductValue;

/**
 * Base field type to define high level types related to a default backend type, a renderer, etc
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFieldFactory
{

    const FIELD_DATETIME = 'datetime';
    const FIELD_DECIMAL  = 'decimal';
    const FIELD_IMAGE    = 'image';
    const FIELD_INTEGER  = 'integer';
    const FIELD_SELECT   = 'select';
    const FIELD_STRING   = 'string';
    const FIELD_TEXT     = 'text';

    /**
     * Return available type
     * @return array
     */
    public static function getTypeOptions()
    {
        // TODO : add others, gow to add custom
        return array(
            self::FIELD_STRING => self::FIELD_STRING,
            self::FIELD_TEXT   => self::FIELD_TEXT,
            self::FIELD_SELECT => self::FIELD_SELECT,
        );
    }

    /**
     * Get doctrine mapping for base fields
     * @see http://docs.doctrine-project.org/en/2.0.x/reference/basic-mapping.html
     * @param string $fieldType
     * @return string
     */
    public function getDoctrineMapping($fieldType)
    {
        // TODO: update, complete ...
        if ($fieldType == self::FIELD_INTEGER or $fieldType == self::FIELD_SELECT) {
            return 'integer';
        } else if ($fieldType == self::FIELD_DATETIME) {
            return 'datetime';
        } else if ($fieldType == self::FIELD_DECIMAL) {
            return 'decimal';
        } else if ($fieldType == self::FIELD_TEXT) {
            return 'text';
        } else {
            return 'string';
        }
    }

}