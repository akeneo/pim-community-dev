<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

/**
 * Common interface for entity transformers
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityTransformerInterface
{
    /**
     * Transforms an array into an entity
     *
     * @param string $class
     * @param array  $data
     * @param array  $defaults
     *
     * @return object
     */
    public function transform($class, array $data, array $defaults = array());

    /**
     * Return infos about the last imported columns
     *
     * @param string $class
     *
     * @return array
     */
    public function getTransformedColumnsInfo($class);

    /**
     * Returns the errors for the last imported entity
     *
     * @param string $class
     *
     * @return array
     */
    public function getErrors($class);
}
