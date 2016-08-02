<?php

namespace Akeneo\Component\StorageUtils\Updater;

/**
 * Copy the property of an object to another object property
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal
 */
interface PropertyCopierInterface
{
    /**
     * Copy a data from a source property to a destination property (erase the current destination data)
     *
     * @param object $fromObject   The object to read from
     * @param object $toObject     The object to update
     * @param string $fromProperty The property to read from
     * @param string $toProperty   The property to update
     * @param array  $options      Options to pass to the copier
     *
     * @throws \InvalidArgumentException
     *
     * @return PropertyCopierInterface
     *
     * @internal
     */
    public function copyData(
        $fromObject,
        $toObject,
        $fromProperty,
        $toProperty,
        array $options = []
    );
}
