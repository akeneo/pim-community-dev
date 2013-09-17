<?php

namespace Oro\Bundle\BatchBundle\Item;

use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * Interface to provide data.
 *
 * Implementation are expected to be stateful and will be called multiple times
 * for each batch, with each call to read() returning a different value and
 * finally returning null when all input data is exhausted.
 *
 * Inspired by Spring Batch  org.springframework.batch.item.ItemReader
 *
 */
interface ItemReaderInterface
{
    /**
     * Reads a piece of input data and advance to the next one. Implementations
     * <strong>must</strong> return <code>null</code> at the end of the input
     * data set.
     *
     * @throws ParseException if there is a problem parsing the current record
     * (but the next one may still be valid)
     * @throws NonTransientResourceException if there is a fatal exception in
     * the underlying resource. After throwing this exception implementations
     * should endeavour to return null from subsequent calls to read.
     * @throws UnexpectedInputException if there is an uncategorised problem
     * with the input data. Assume potentially transient, so subsequent calls to
     * read might succeed.
     * @throws \Exception if an there is a non-specific error.
     * @return null|mixed Returns false in case of reading error
     */
    public function read(StepExecution $stepExecution);
}
