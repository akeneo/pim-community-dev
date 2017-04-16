<?php

namespace Pim\Component\Catalog\ProductValue;

use Pim\Component\Catalog\Model\MetricInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Interface for metric product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MetricProductValueInterface extends ProductValueInterface
{
    /**
     * @return MetricInterface|null
     */
    public function getData();

    /**
     * @return float|null
     */
    public function getAmount();

    /**
     * @return string|null
     */
    public function getUnit();
}
