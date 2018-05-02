<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * Classes that implement this interface have to handle invalid items raised in the Processor, Reader and Writer.
 *
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface InvalidItemInterface
{
    /**
     * Get the invalid data
     *
     * @return mixed
     */
    public function getInvalidData();
}
