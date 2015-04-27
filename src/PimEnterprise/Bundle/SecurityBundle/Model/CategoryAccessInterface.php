<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category access interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface CategoryAccessInterface extends AccessInterface
{
    /**
     * @param CategoryInterface $category
     *
     * @return CategoryAccessInterface
     */
    public function setCategory(CategoryInterface $category);

    /**
     * @return CategoryInterface
     */
    public function getCategory();

    /**
     * @param bool $editProducts
     *
     * @return CategoryAccessInterface
     */
    public function setEditProducts($editProducts);

    /**
     * @return bool
     */
    public function isEditProducts();

    /**
     * @param bool $viewProducts
     *
     * @return CategoryAccessInterface
     */
    public function setViewProducts($viewProducts);

    /**
     * @return bool
     */
    public function isViewProducts();

    /**
     * @param bool $ownProducts
     *
     * @return CategoryAccessInterface
     */
    public function setOwnProducts($ownProducts);

    /**
     * @return bool
     */
    public function isOwnProducts();
}
