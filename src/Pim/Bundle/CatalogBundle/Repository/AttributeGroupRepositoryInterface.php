<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\AttributeGroupInterface;

/**
 * Attribute group repository interface
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeGroupRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * @return AttributeGroupInterface
     */
    public function findAllWithTranslations();

    /**
     * Find all ordered by label with fallback to default mechanism
     *
     * @return array
     */
    public function getIdToLabelOrderedBySortOrder();

    /**
     * Get the attribute group choices
     *
     * @deprecated only used in grid, will be removed in 1.6
     *
     * @return array
     */
    public function getAttributeGroupChoices();

    /**
     * Get the default attribute group
     *
     * @return null|AttributeGroupInterface
     */
    public function findDefaultAttributeGroup();

    /**
     * @param array $codes
     *
     * @return AttributeGroupInterface
     */
    public function getAttributeGroupsFromAttributeCodes(array $codes);

    /**
     * Find the largest attribute group sort order present in the database
     *
     * @return int
     */
    public function getMaxSortOrder();
}
