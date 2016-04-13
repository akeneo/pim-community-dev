<?php

namespace Akeneo\Component\Batch\Item;

/**
 * FlushableInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface FlushableInterface
{
    /**
     * Custom logic on step completion.
     */
    public function flush();
}
