<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileMetadata;

/**
 * FileMetadataBagInterface is a container for recursive key/value pairs.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
interface FileMetadataBagInterface
{
    /**
     * Adds data. Existing keys are replaced recursively.
     *
     * @param array $data
     */
    public function add(array $data);

    /**
     * Returns a data by the given $path. If no value is found, returns $default
     * instead.
     *
     * Searches recursively using the dot notation, case insensitive.
     * If the key contains a dot, please escape the dot with an antislash '\'.
     *
     * Example:
     * [
     *     'FirstOne' => [
     *         'Deep'          => 'myData'
     *         'Dot.Separated' => 'Hello'
     *     ]
     * ]
     *
     * get('FirstOne.Deep') => 'myData'
     * get('firstone.deep') => 'myData'
     * get('FirstOne.Dot\.Separated') => 'Hello'
     *
     * @param string $path
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($path, $default = null);

    /**
     * Returns whether or not the value exists/is not null for the
     * given $path. Uses the dot notation.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has($path);

    /**
     * Returns all data as an array.
     *
     * @return array
     */
    public function all();
}
