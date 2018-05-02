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
interface ItemReaderInterface
{
    /**
     * Reads a piece of input data and advance to the next one. Implementations
     * <strong>must</strong> return <code>null</code> at the end of the input
     * data set.
     *
     * @throws InvalidItemException if there is a problem reading the current record
     *                              (but the next one may still be valid)
     * @throws \Exception           if an there is a non-specific error. (step execution will
     *                              be stopped in that case)
     *
     * @return null|mixed
     */
    public function read();
}
