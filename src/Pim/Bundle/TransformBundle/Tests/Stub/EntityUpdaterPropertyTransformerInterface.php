<?php

namespace Pim\Bundle\TransformBundle\Tests\Stub;

use Pim\Bundle\TransformBundle\Transformer\Property\EntityUpdaterInterface;
use Pim\Bundle\TransformBundle\Transformer\Property\PropertyTransformerInterface;

/**
 * Stub interface
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityUpdaterPropertyTransformerInterface extends PropertyTransformerInterface, EntityUpdaterInterface
{
}
