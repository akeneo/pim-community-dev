<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Updater;

/**
 * Clears the data of an object.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertyClearerInterface
{
    /**
     * Clears the data in a object property.
     *
     * @param object $object   The object to update
     * @param string $property The property to clear
     * @param array  $options  Options to pass to the clearer
     *
     * @throws \InvalidArgumentException
     */
    public function clear($object, string $property, array $options = []): void;
}
