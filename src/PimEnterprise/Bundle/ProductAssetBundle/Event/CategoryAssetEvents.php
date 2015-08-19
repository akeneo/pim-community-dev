<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Event;

/**
 * Category asset events
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
final class CategoryAssetEvents
{
    /**
     * This event is thrown before a category gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE_CATEGORY = 'pimee_product_asset.pre_remove.category';

    /**
     * This event is thrown after a category gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE_CATEGORY = 'pimee_product_asset.post_remove.category';

    /**
     * This event is thrown before a tree gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const PRE_REMOVE_TREE = 'pimee_product_asset.pre_remove.tree';

    /**
     * This event is thrown after a tree gets removed.
     *
     * The event listener receives an
     * Akeneo\Component\StorageUtils\Event\RemoveEvent instance.
     *
     * @staticvar string
     */
    const POST_REMOVE_TREE = 'pimee_product_asset.post_remove.tree';
}
