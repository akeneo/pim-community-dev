<?php

namespace Akeneo\Tool\Component\Classification;

use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Implementing this interface allows to be aware of categories
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CategoryAwareInterface
{
    /**
     * @return ArrayCollection of CategoryInterface
     */
    public function getCategories();

    /**
     * @param CategoryInterface $category
     *
     * @return mixed
     */
    public function removeCategory(CategoryInterface $category);

    /**
     * @param CategoryInterface $category
     *
     * @return mixed
     */
    public function addCategory(CategoryInterface $category);

    /**
     * Get a string with categories linked to the entity
     *
     * @return array
     */
    public function getCategoryCodes();

    /**
     * @param Collection $categories
     */
    public function setCategories(Collection $categories): void;
}
