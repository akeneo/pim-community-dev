<?php

namespace Pim\Bundle\BatchBundle\Item;

/**
 * Exception thrown when there is a problem parsig the current record,
 * (but the next one may still be valid)
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParseException extends \Exception
{
}
