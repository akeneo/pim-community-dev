<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Item;

/**
 * Exception thrown when there is a problem parsig the current record,
 * (but the next one may still be valid)
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class ParseException extends \Exception
{
}
