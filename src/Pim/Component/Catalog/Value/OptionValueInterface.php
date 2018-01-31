<?php

namespace Pim\Component\Catalog\Value;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Interface for option product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OptionValueInterface extends ValueInterface
{
    /**
     * @return AttributeOptionInterface|null
     */
    public function getData();
}
