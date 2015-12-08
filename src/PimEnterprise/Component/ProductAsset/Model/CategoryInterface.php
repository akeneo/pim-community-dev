<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Model;

use Akeneo\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Pim\Component\Catalog\Model\ReferableInterface;

/**
 * Category interface
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface CategoryInterface extends
    BaseCategoryInterface,
    TranslatableInterface,
    ReferableInterface,
    VersionableInterface
{
    /**
     * Predicate to know if this category has asset(s) linked
     *
     * @return bool
     */
    public function hasAssets();

    /**
     * Get assets for this category node
     *
     * @return AssetInterface[]
     */
    public function getAssets();
}
