<?php

namespace Akeneo\Component\FileMetadata\Adapter;

/**
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
        $this->registry =$registry;
    }

    /**
     * @param null $mimeType
     *
     * @return array
     */
    public function dump($mimeType = null)
    {
        $output = [];

        foreach ($this->registry->all() as $adapter) {
            if (null === $mimeType || (null !== $mimeType && $adapter->isMimeTypeSupported($mimeType))) {
                $output[$adapter->getName()] = [
                    'class' => get_class($adapter),
                    'mimeTypes' => implode(', ', $adapter->getSupportedMimeTypes())
                ];
            }
        }

        return $output;
    }
}
