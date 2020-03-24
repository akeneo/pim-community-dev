<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Updater;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertyClearerInterface
{
    /**
     * Sets a data in a object property (erase the current data)
     *
     * @param object $object   The object to update
     * @param string $property The property to update
     * @param array  $options  Options to pass to the setter
     *
     * @throws \InvalidArgumentException
     */
    public function clear($object, string $property, array $options = []): void;
}
