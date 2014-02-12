<?php

namespace Akeneo\Bundle\BatchBundle\Item;

/**
 * Exception thrown when there is a problem parsig the current record,
 * (but the next one may still be valid)
 *
 */
class ParseException extends \Exception
{
}
