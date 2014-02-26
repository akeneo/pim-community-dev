<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\ORM\QueryBuilder;

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
     * @param integer $id
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function getFullProduct($id);

    /**
     * Return the number of times the product is present in each tree
     *
     * @param ProductInterface $product The product to look for in the trees
     *
     * @return array Each row of the array has the format:'tree'=>treeObject, 'productCount'=>integer
     */
    public function getProductCountByTree(ProductInterface $product);

    /**
     * Get product ids linked to a category or its children.
     * You can define if you just want to get the property of the actual node or with its children with the direct
     * parameter
     *
     * @param CategoryInterface $category   the requested node
     * @param QueryBuilder      $categoryQb category query buider
     *
     * @return array
     */
    public function getProductIdsInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null);

    /**
     * Count products linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param CategoryInterface $category   the requested category node
     * @param QueryBuilder      $categoryQb category query buider
     *
     * @return integer
     */
    public function getProductsCountInCategory(CategoryInterface $category, QueryBuilder $categoryQb = null);

    /**
     * Count products per channel
     * It returns the same set of products to export, but doesn't consider the completeness ratio,
     * and group them by channel
     * Example:
     *    array(
     *        array(
     *            'label' => 'Mobile',
     *            'total' => 100,
     *        ),
     *        array(
     *            'label' => 'E-Commerce',
     *            'total' => 85,
     *        ),
     *    )
     *
     * @return array
     */
    public function countProductsPerChannels();

    /**
     * Count complete products per channel and locales
     * It returns the same set of products to export and group them by channel and locale
     * Example:
     *    array(
     *        array(
     *            'label' => 'Mobile',
     *            'code' => 'en_US',
     *            'total' => 10,
     *        ),
     *        array(
     *            'label' => 'E-Commerce',
     *            'code' => 'en_US',
     *            'total' => 85,
     *        ),
     *        array(
     *            'label' => 'Mobile',
     *            'code' => 'fr_FR',
     *            'total' => 5,
     *        ),
     *        array(
     *            'label' => 'E-Commerce',
     *            'code' => 'fr_FR',
     *            'total' => 63,
     *        ),
     *    )
     *
     * @return array
     */
    public function countCompleteProductsPerChannels();

    /**
     * Returns true if a ProductValue with the provided value alread exists,
     * false otherwise.
     *
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function valueExists(ProductValueInterface $value);
}
