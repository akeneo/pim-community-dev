<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Attribute\Type;

/**
 * Abstract attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractAttributeType
{

    /**
     * Available backend model
     * @var string
     */
    const BACKEND_STORAGE_ATTRIBUTE_VALUE = 'values'; // TODO rename in 'attributeValues';
    const BACKEND_STORAGE_FLAT_VALUE      = 'flatValues';

    /**
     * Available backend types
     * @var string
     */
    const BACKEND_TYPE_DATE     = 'date';
    const BACKEND_TYPE_DATETIME = 'datetime';
    const BACKEND_TYPE_DECIMAL  = 'decimal';
    const BACKEND_TYPE_OPTION   = 'option';
    const BACKEND_TYPE_INTEGER  = 'integer';
    const BACKEND_TYPE_VARCHAR  = 'varchar';
    const BACKEND_TYPE_TEXT     = 'text';

}
