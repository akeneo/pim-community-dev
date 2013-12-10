<?php

namespace Pim\Bundle\ImportExportBundle\Transformer\Property;

/**
 * Interface for association transformers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationTransformerInterface extends PropertyTransformerInterface
{
    /**
     * Returns an object for a given class and code
     *
     * @param string $class
     * @param string $value
     *
     * @return object
     */
    public function getEntity($class, $value);
}
