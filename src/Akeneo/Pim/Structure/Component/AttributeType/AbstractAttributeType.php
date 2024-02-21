<?php

namespace Akeneo\Pim\Structure\Component\AttributeType;

use Akeneo\Pim\Structure\Component\AttributeTypeInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * Abstract attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeType implements AttributeTypeInterface
{
    /**
     * Field backend type, "text" by default, the doctrine mapping field, getter / setter to use for binding
     *
     * @var string
     */
    protected $backendType = AttributeTypes::BACKEND_TYPE_TEXT;

    /**
     * @param string $backendType the backend type
     */
    public function __construct($backendType)
    {
        $this->backendType = $backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->backendType;
    }

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return false;
    }
}
