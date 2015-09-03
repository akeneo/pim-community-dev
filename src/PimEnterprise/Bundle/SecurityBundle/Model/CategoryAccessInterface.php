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

use Akeneo\Component\Classification\Model\CategoryInterface;

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
     * @param bool $editItems
     *
     * @return CategoryAccessInterface
     */
    public function setEditItems($editItems);

    /**
     * @return bool
     */
    public function isEditItems();

    /**
     * @param bool $viewItems
     *
     * @return CategoryAccessInterface
     */
    public function setViewItems($viewItems);

    /**
     * @return bool
     */
    public function isViewItems();

    /**
     * @param bool $ownItems
     *
     * @return CategoryAccessInterface
     */
    public function setOwnItems($ownItems);

    /**
     * @return bool
     */
    public function isOwnItems();
}
