<?php

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

interface CategoryAccessInterface extends AccessInterface
{
    /**
     * @param CategoryInterface $category
     *
     * @return CategoryAccessInterface
     */
    public function setCategory($category);

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
    public function getEditProducts();

    /**
     * @param boolean $viewProducts
     *
     * @return CategoryAccessInterface
     */
    public function setViewProducts($viewProducts);

    /**
     * @return boolean
     */
    public function getViewProducts();
}
