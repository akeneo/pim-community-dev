<?php

namespace Pim\Bundle\TransformBundle\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface;

/**
 * Extra interface for property transformers which need specific treatment
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityUpdaterInterface
{
    /**
     * Updates the object instance
     *
     * @param object              $object
     * @param ColumnInfoInterface $columnInfo
     * @param mixed               $data
     * @param array               $options
     */
    public function setValue($object, ColumnInfoInterface $columnInfo, $data, array $options = array());
}
