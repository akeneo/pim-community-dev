<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Value;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Pim\Component\Catalog\Model\MetricInterface;

/**
 * Interface for metric product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MetricValueInterface extends ValueInterface
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
