<?php

namespace Akeneo\Component\Buffer;

use Akeneo\Component\Buffer\Exception\InvalidClassNameException;

/**
 * Basic implementation of BufferFactoryInterface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class BufferFactory implements BufferFactoryInterface
{
    /** @var string */
    protected $className;

    /**
     * @param string $className
     *
     * @throws InvalidClassNameException
     */
    public function __construct($className)
    {
        $interface = '\Akeneo\Component\Buffer\BufferInterface';
        if (!is_subclass_of($className, $interface)) {
            throw new InvalidClassNameException(sprintf('%s must implement %s', $className, $interface));
        }

        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->className();
    }
}
