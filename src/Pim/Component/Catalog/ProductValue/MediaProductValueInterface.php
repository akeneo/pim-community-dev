<?php

namespace Pim\Component\Catalog\ProductValue;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Interface for media product value
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MediaProductValueInterface extends ProductValueInterface
{
    /**
     * @return FileInfoInterface|null
     */
    public function getData();
}
