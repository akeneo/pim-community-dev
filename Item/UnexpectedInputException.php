<?php

namespace Akeneo\Bundle\BatchBundle\Item;

/**
 * if there is an uncategorised problem
 * with the input data. Assume potentially transient, so subsequent calls to
 * read might succeed.
 *
 */
class UnexpectedInputException extends \Exception
{
}
