<?php

namespace Pim\Component\Catalog\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;

/**
 * Product value, business code is in AbstractProductValue, this class can be overriden in projects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @ExclusionPolicy("all")
 */
class ProductValue extends AbstractProductValue
{
}
