<?php

namespace Akeneo\Tool\Component\Batch\Item;

/**
 * Interface for generic output operations.
 *
 * Class implementing this interface will be responsible for serializing
 * objects as necessary.
 * Generally, it is responsibility of implementing class to decide which
 * technology to use for mapping and how it should be configured.
 *
 * The write method is responsible for making sure that any internal buffers are
 * flushed. If a transaction is active it will also usually be necessary to
 * discard the output on a subsequent rollback. The resource to which the writer
 * is sending data should normally be able to handle this itself.
 *
 * Inspired by Spring Batch  org.springframework.batch.item.ItemWriter
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ItemWriterInterface
{
    /**
     * Process the supplied data element. Will not be called with any null items
     * in normal operation.
     *
     * @param array $items The list of items to write
     *
     * FIXME: array is not maybe the best structure to hold the items. Investigate this point.
     *
     * @throw InvalidItemException if there is a warning, step execution will continue to the
     * next item.
     * @throws \Exception if there are errors. The framework will catch the
     *                    exception and convert or rethrow it as appropriate.
     */
    public function write(array $items);
}
