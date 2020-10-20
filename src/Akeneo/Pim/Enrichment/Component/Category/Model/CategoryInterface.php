<?php

namespace Akeneo\Pim\Enrichment\Component\Category\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Category interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryInterface extends
    BaseCategoryInterface,
    TranslatableInterface,
    ReferableInterface,
    VersionableInterface
{
    /**
     * Predicate to know if this category has product(s) linked
     *
     * @return bool
     */
    public function hasProducts();

    /**
     * Get products for this category node
     *
     * @return ProductInterface[]
     */
    public function getProducts();

    /**
     * Predicate to know if this category has product model(s) linked
     *
     * @return bool
     */
    public function hasProductModels(): bool;

    /**
     * Get product models for this category node
     *
     * @return Collection of ProductModelInterface
     */
    public function getProductModels(): Collection;
}
