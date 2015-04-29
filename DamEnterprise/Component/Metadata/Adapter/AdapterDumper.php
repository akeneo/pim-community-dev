<?php

namespace DamEnterprise\Component\Metadata\Adapter;

class AdapterDumper
{
    /** @var AdapterRegistry */
    protected $registry;

    public function __construct(AdapterRegistry $registry)
    {
        $this->registry =$registry;
    }

    public function dump($mimeType = null)
    {
        $output = [];

        foreach ($this->registry->all() as $adapter) {
            if (null === $mimeType || (null !== $mimeType && $adapter->supportsMimeType($mimeType))) {
                $output[$adapter->getName()] = [
                    'class' => get_class($adapter),
                    'mimeTypes' => implode(', ', $adapter->getMimeTypes())
                ];
            }
        }

        return $output;
    }
}
