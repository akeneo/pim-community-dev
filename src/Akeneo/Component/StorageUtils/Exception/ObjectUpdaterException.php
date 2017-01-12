<?php

namespace Akeneo\Component\StorageUtils\Exception;

/**
 * This exception is the root exception for updaters.
 * It can be thrown by an updater when performing an action on an object.
 * Updaters should not throw any other exception.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ObjectUpdaterException extends \LogicException
{
}
