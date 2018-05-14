<?php

namespace Akeneo\Tool\Component\Buffer\Exception;

/**
 * Exception thrown when trying to configure a BufferFactory with a class name that does not implement BufferInterface.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidClassNameException extends \InvalidArgumentException
{
}
