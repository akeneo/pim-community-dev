<?php

namespace Pim\Component\Catalog\Model;

use Akeneo\Component\Classification\Model\CategoryInterface as BaseCategoryInterface;
use Akeneo\Component\Versioning\Model\VersionableInterface;
use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;

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
}
