<?php

namespace Akeneo\Bundle\BatchBundle\Item;

/**
 * if there is a fatal exception in
 * the underlying resource. After throwing this exception implementations
 * should endeavour to return null from subsequent calls to read.
 *
 */
class NonTransientResourceException extends \Exception
{
}
