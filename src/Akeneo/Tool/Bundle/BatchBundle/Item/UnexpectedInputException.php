<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Item;

/**
 * if there is an uncategorised problem
 * with the input data. Assume potentially transient, so subsequent calls to
 * read might succeed.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class UnexpectedInputException extends \Exception
{
}
