<?php

namespace Akeneo\Tool\Bundle\BatchBundle\Item\Support;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;

/**
 * Very basic sample transformer that will put the first letter of each item in uppercase
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class UcfirstProcessor implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return ucfirst($item);
    }
}
