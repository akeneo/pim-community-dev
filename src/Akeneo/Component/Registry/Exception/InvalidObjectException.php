<?php

namespace Akeneo\Component\Registry\Exception;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidObjectException extends \InvalidArgumentException
{
    public function __construct($object, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('The object of type "%s" are accepted, wrong type given.', $object));
    }
}
