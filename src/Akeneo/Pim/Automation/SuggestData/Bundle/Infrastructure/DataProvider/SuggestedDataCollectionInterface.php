<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

use Countable;
use Iterator;

/**
 * Object representing a collection of suggested data from PIM.ai.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface SuggestedDataCollectionInterface extends Iterator, Countable
{
    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @param SuggestedDataInterface $suggestedData
     */
    public function add(SuggestedDataInterface $suggestedData): void;

    /**
     * Returns false if index does not exist, true if well removed.
     *
     * @param int $index
     *
     * @return bool
     */
    public function remove(int $index): bool;
}
