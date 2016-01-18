<?php

namespace Pim\Bundle\BaseConnectorBundle\Exception;

use Pim\Component\Connector\Exception\CharsetException as NewCharsetException;

/**
 * Exception thrown when a file is not well encoded.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CharsetException extends NewCharsetException
{
}
