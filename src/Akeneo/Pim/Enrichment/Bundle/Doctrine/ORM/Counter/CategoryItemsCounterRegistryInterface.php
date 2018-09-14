<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter;

/**
 * Category items counter registry interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryItemsCounterRegistryInterface
{
    /**
     * Register a category item counter extension
     *
     * @param CategoryItemsCounterInterface $categoryItemsCounter
     * @param string                        $type
     *
     * @return mixed
     */
    public function register(CategoryItemsCounterInterface $categoryItemsCounter, $type);

    /**
     * Get category item counter extension
     *
     * @param string $name
     *
     * @return CategoryItemsCounterInterface
     */
    public function get($name);
}
