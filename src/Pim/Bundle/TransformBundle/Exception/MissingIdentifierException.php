<?php

namespace Pim\Bundle\TransformBundle\Exception;

use Pim\Component\Connector\Exception\MissingIdentifierException as NewMissingIdentifierException;

/**
 * Exception thrown when the identifier column is unknown
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MissingIdentifierException extends NewMissingIdentifierException
{
}
