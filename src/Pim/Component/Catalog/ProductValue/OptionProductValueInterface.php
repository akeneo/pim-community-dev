<?php

namespace Pim\Component\Catalog\ProductValue;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Interface for option product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OptionProductValueInterface extends ProductValueInterface
{
    /**
     * @return AttributeOptionInterface|null
     */
    public function getData();
}
