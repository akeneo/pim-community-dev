<?php

namespace Pim\Component\Catalog\ProductValue;

use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Interface for options product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OptionsProductValueInterface extends ProductValueInterface
{
    /**
     * @return AttributeOptionInterface[]
     */
    public function getData();

    /**
     * @param string $code
     *
     * @return bool
     */
    public function hasCode($code);

    /**
     * @return array
     */
    public function getOptionCodes();
}
