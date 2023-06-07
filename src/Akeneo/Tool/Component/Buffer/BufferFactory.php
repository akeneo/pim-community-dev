<?php

namespace Akeneo\Tool\Component\Buffer;

use Akeneo\Tool\Component\Buffer\Exception\InvalidClassNameException;

/**
 * Creates instances of BufferInterface implementations.
 * The main goal of this factory is to be injected into any object that needs it then
 * instanciate a buffer, this way it is impossible that several objects share the same
 * buffer instance (for obvious unpredictable behavior issues).
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BufferFactory
{
    /** @var string */
    protected $className;

    /**
     * Configure the factory with a class name
     *
     * @param string $className          The class name
     *
     * @throws InvalidClassNameException If the class name is not a implementation of BufferInterface
     */
    public function __construct($className)
    {
        $interface = BufferInterface::class;
        if (!is_subclass_of($className, $interface)) {
            throw new InvalidClassNameException(sprintf('%s must implement %s', $className, $interface));
        }

        $this->className = $className;
    }

    /**
     * Create a buffer instance
     */
    public function create()
    {
        return new $this->className();
    }
}
