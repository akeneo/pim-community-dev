<?php

namespace Pim\Bundle\BaseConnectorBundle\Validator\Step;

use Pim\Component\Connector\Item\CharsetValidator as NewCharsetValidator;

/**
 * Check the encoding of a file.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.5, please use Pim\Component\Connector\Item\CharsetValidator
 */
class CharsetValidator extends NewCharsetValidator
{
}
