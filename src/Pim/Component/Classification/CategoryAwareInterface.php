<?php

// TODO-4251: Pim\Component\Classification to Akeneo\Component\Classification, once the feat-pam branch merged
namespace Pim\Component\Classification;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Classification\Model\CategoryInterface;

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
     * @return string
     */
    public function getCategoryCodes();
}
