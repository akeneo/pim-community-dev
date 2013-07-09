<?php

namespace Pim\Bundle\BatchBundle\Item;

/**
 * Interface for item transformation.  Given an item as input, this interface provides
 * an extension point which allows for the application of business logic in an item
 * oriented processing scenario.  It should be noted that while it's possible to return
 * a different type than the one provided, it's not strictly necessary.  Furthermore,
 * returning null indicates that the item should not be continued to be processed.
 *
 * Inspired by Spring Batch  org.springframework.batch.item.ItemProcessor
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface ItemProcessorInterface
{
    /**
     * Process the provided item, returning a potentially modified or new item for continued
     * processing.  If the returned result is null, it is assumed that processing of the item
     * should not continue.
     *
     * @param mixed $item item to be processed
     *
     * @return potentially modified or new item for continued processing, null if processing of the
     *  provided item should not continue.
     * @throws Exception
     */
    public function process($item);
}
