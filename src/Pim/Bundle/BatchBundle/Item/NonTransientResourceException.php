<?php

namespace Pim\Bundle\BatchBundle\Item;

/**
 * if there is a fatal exception in
 * the underlying resource. After throwing this exception implementations
 * should endeavour to return null from subsequent calls to read.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonTransientResourceException extends \Exception
{
}
