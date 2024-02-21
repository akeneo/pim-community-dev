<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Exception thrown when performing an action on a property with invalid attribute.
 *
 * @author    Julien Janvier <julien.jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidAttributeException extends InvalidPropertyException
{
}
