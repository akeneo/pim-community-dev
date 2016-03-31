<?php

namespace Pim\Component\Connector\Reader\File;

use Pim\Component\Connector\Reader\File\FileIteratorInterface;

class FileIteratorFactory
{
    /** @var string */
    protected $className;

    /** @var string */
    protected $type;

    /**
     * Configure the factory with a class name
     *
     * @param string $className
     * @param string $type
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($className, $type)
    {
        $interface = '\Pim\Component\Connector\Reader\File\FileIteratorInterface';
        if (!is_subclass_of($className, $interface)) {
            throw new \InvalidArgumentException(sprintf('%s must implement %s', $className, $interface));
        }

        $this->className = $className;
        $this->type      = $type;
    }

    /**
     * Create a file iterator instance
     *
     * @param string $filePath
     * @param array  $options
     *
     * @return FileIteratorInterface
     */
    public function create($filePath, array $options = [])
    {
        return new $this->className($this->type, $filePath, $options);
    }
}
