<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Component\FileMetadata\Adapter;

/**
 * Dumps all the adapters that are registered
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class AdapterDumper
{
    /** @var AdapterRegistry */
    protected $registry;

    /**
     * @param AdapterRegistry $registry
     */
    public function __construct(AdapterRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Dumps all the adapters that are registered
     *
     * @param string|null $mimeType
     *
     * @return array
     */
    public function dump($mimeType = null)
    {
        $output = [];

        foreach ($this->registry->all() as $adapter) {
            if (null === $mimeType || (null !== $mimeType && $adapter->isMimeTypeSupported($mimeType))) {
                $output[$adapter->getName()] = [
                    'class'     => get_class($adapter),
                    'mimeTypes' => implode(', ', $adapter->getSupportedMimeTypes())
                ];
            }
        }

        return $output;
    }
}
