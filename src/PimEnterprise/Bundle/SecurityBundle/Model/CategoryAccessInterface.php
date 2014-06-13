<?php

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category access interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
     * @param boolean $editProducts
     *
     * @return CategoryAccessInterface
     */
    public function setEditProducts($editProducts);

    /**
     * @return boolean
     */
    public function isEditProducts();

    /**
     * @param boolean $viewProducts
     *
     * @return CategoryAccessInterface
     */
    public function setViewProducts($viewProducts);

    /**
     * @return boolean
     */
    public function isViewProducts();
}
