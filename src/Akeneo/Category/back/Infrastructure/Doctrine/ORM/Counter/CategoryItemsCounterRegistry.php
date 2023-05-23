<?php

namespace Akeneo\Category\Infrastructure\Doctrine\ORM\Counter;

use Akeneo\Category\Infrastructure\Component\CategoryItemsCounterInterface;

/**
 * Category items counter registry.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryItemsCounterRegistry
{
    /** @var CategoryItemsCounterInterface[] */
    protected static $categoryItemsCounter = [];

    /**
     * Register a category item counter extension.
     *
     * @param string $type
     *
     * @return mixed
     */
    public function register(CategoryItemsCounterInterface $categoryItemsCounter, $type)
    {
        self::$categoryItemsCounter[$type] = $categoryItemsCounter;

        return $this;
    }

    /**
     * Get category item counter extension.
     *
     * @param string $name
     *
     * @return CategoryItemsCounterInterface
     */
    public function get($name)
    {
        return self::$categoryItemsCounter[$name];
    }
}
