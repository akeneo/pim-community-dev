<?php

namespace Pim\Component\Catalog\Value;

use Pim\Component\Catalog\Model\ValueInterface;

/**
 * Interface for date product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DateValueInterface extends ValueInterface
{
    /**
     * @return \DateTime|null
     */
    public function getData();
}
