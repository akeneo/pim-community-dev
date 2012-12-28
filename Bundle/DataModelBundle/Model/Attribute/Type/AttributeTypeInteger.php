<?php
namespace Oro\Bundle\DataModelBundle\Model\Attribute\Type;

/**
 * Attribute type integer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeTypeInteger extends AbstractAttributeType
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(self::BACKEND_TYPE_INTEGER);
    }
}
