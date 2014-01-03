<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Product repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepositoryInterface
{
    /**
     * @param string $scope
     *
     * @return QueryBuilder
     */
    public function buildByScope($scope);

    /**
     * @param Channel $channel
     *
     * @return QueryBuilder
     */
    public function buildByChannelAndCompleteness(Channel $channel);

    /**
     * Find products by existing family
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByExistingFamily();

    /**
     * @param array $ids
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByIds(array $ids);

    /**
     * @return integer[]
     */
    public function getAllIds();

    /**
     * Find all products in a variant group (by variant axis attribute values)
     *
     * @param Group $variantGroup the variant group
     * @param array $criteria     the criteria
     *
     * @return array
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array());

    /**
     * Returns a full product with all relations
     *
     * @param int $id
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function getFullProduct($id);
}
