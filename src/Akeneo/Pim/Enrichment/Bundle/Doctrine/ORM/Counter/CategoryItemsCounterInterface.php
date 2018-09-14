<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;

/**
 * Count items in a category
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryItemsCounterInterface
{
    /**
     * Count items linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param CategoryInterface $category   the requested category node
     * @param bool              $inChildren true to include children in count
     * @param bool              $inProvided true to include the provided none to count item
     *
     * @return int
     */
    public function getItemsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true);
}
