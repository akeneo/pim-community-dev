<?php

namespace Pim\Bundle\BatchBundle\Item;

use Pim\Bundle\BatchBundle\Entity\StepExecution;

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
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @throws Exception if an there is a non-specific error.
     */
    public function read(StepExecution $stepExecution);
}
