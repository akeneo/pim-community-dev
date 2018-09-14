<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Counter;

/**
 * Category items counter registry
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryItemsCounterRegistry implements CategoryItemsCounterRegistryInterface
{
    /** @var CategoryItemsCounterInterface[] */
    protected static $categoryItemsCounter = [];

    /**
     * {@inheritdoc}
     */
    public function register(CategoryItemsCounterInterface $categoryItemsCounter, $type)
    {
        self::$categoryItemsCounter[$type] = $categoryItemsCounter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return self::$categoryItemsCounter[$name];
    }
}
