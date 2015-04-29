<?php

namespace DamEnterprise\Component\Metadata;

use DamEnterprise\Component\Metadata\Adapter\AdapterInterface;

class Metadata implements MetadataInterface
{
    /** @var AdapterInterface[] */
    protected $adapters = [];

    /**
     * @param AdapterInterface[] $adapters
     */
    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function has(\SplFileInfo $file, $key)
//    {
//        foreach ($this->adapters as $adapter) {
//            if ($adapter->has($file, $key)) {
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function get(\SplFileInfo $file, $key)
//    {
//        foreach ($this->adapters as $adapter) {
//            if ($adapter->has($file, $key)) {
//                return $adapter->get($file, $key);
//            }
//        }
//
//        throw new MetadataNotFoundException();
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function set(\SplFileInfo $file, $key, $value)
//    {
//        foreach ($this->adapters as $adapter) {
//            if ($adapter->supports($key)) {
//                return $adapter->set($file, $key, $value);
//            }
//        }
//
//        throw new MetadataNotSupportedException();
//    }

    public function all(\SplFileInfo $file)
    {
        $metadata = [];
        foreach ($this->adapters as $adapter) {
            $metadata = array_merge($metadata, $adapter->all($file));
        }

        return $metadata;
    }
}
