<?php
namespace Oro\Bundle\DataModelBundle\Model\Attribute\Type;

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

    /**
     * Attribute type backend type
     * @var string
     */
    protected $backendType;

    /**
     * Constructor
     * @param string $backendType
     */
    public function __construct($backendType)
    {
        $this->backendType = $backendType;
    }

    /**
     * Get backend type
     *
     * @return string
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * Set backend type
     * @param string $type
     *
     * @return AbstractAttributeType
     */
    public function setBackendType($type)
    {
        $this->backendType = $type;

        return $this;
    }

    /**
     * To string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->backendType;
    }
}
