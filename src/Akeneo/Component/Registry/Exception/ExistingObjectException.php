<?php

namespace Akeneo\Component\Registry\Exception;

use Exception;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingObjectException extends \InvalidArgumentException
{
    public function __construct($type, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('The object of type "%s" already exists.', $type));
    }
}
