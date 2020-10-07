<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * Interface to provide data.
 *
 * Implementation are expected to be stateful and will be called multiple times
 * for each batch, with each call to read() returning a different value and
 * finally returning null when all input data is exhausted.
 *
 * Inspired by Spring Batch  org.springframework.batch.item.ItemReader
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface TrackableItemReaderInterface
{
    public function count(): int;
}
