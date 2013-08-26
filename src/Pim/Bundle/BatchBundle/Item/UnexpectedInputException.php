<?php

namespace Pim\Bundle\BatchBundle\Item;

/**
 * if there is an uncategorised problem
 * with the input data. Assume potentially transient, so subsequent calls to
 * read might succeed.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnexpectedInputException extends \Exception
{
}
