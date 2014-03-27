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
interface ProductRepositoryInterface extends LocalizableInterface, ScopableInterface
{
    /**
     * Get entity configuration
     *
     * @return array $config
     */
    public function getConfiguration();

    /**
     * Set entity configuration
     *
     * @param array $config
     *
     * @return ProductRepositoryInterface
     */
    public function setConfiguration($config);

    /**
     * Return asked locale code or default one
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return ProductRepositoryInterface
     */
    public function setLocale($code);

    /**
     * Return asked scope code or default one
     *
     * @return string
     */
    public function getScope();

    /**
     * Set scope code
     *
     * @param string $code
     *
     * @return ProductRepositoryInterface
     */
    public function setScope($code);

    /**
     * Finds entities and attributes values by a set of criteria, same coverage than findBy
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    public function findAllByAttributes(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    );

    /**
     * Load a flexible entity with related attribute values
     *
     * @param integer $id
     *
     * @return Product|null
     * @throws NonUniqueResultException
     */
    public function findOneByWithValues($id);

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

    /**
     * Set flexible query builder
     *
     * @param ProductQueryBuilder $productQB
     *
     * @return ProductRepositoryInterface
     */
    public function setProductQueryBuilder($productQB);

    /**
     * Apply a filter by product ids
     *
     * @param mixed   $qb         query builder to update
     * @param array   $productIds product ids
     * @param boolean $include    true for in, false for not in
     */
    public function applyFilterByIds($qb, array $productIds, $include);

    /**
     * Apply a filter by attribute value
     *
     * @param mixed             $qb             query builder to update
     * @param AbstractAttribute $attribute      attribute
     * @param string|array      $attributeValue value(s) used to filter
     * @param string            $operator       operator to use
     */
    public function applyFilterByAttribute($qb, AbstractAttribute $attribute, $attributeValue, $operator = '=');

    /**
     * Apply a filter by a field value
     *
     * @param mixed        $qb       query builder to update
     * @param string       $field    the field
     * @param string|array $value    value(s) used to filter
     * @param string       $operator operator to use
     */
    public function applyFilterByField($qb, $field, $value, $operator = '=');

    /**
     * Apply a sort by attribute value
     *
     * @param mixed             $qb        query builder to update
     * @param AbstractAttribute $attribute attribute
     * @param string            $direction direction to use
     */
    public function applySorterByAttribute($qb, AbstractAttribute $attribute, $direction);

    /**
     * Apply a sort by field value
     *
     * @param mixed  $qb        query builder to update
     * @param string $field     the field to sort by
     * @param string $direction direction to use
     */
    public function applySorterByField($qb, $field, $direction);

    /**
     * Delete a list of product ids
     *
     * @param integer[] $ids
     *
     * @throws \LogicException
     */
    public function deleteFromIds(array $ids);

    /**
     * Apply mass action parameters on query builder
     *
     * @param mixed   $qb
     * @param boolean $inset
     * @param array   $values
     */
    public function applyMassActionParameters($qb, $inset, $values);

    /**
     * Get available attribute ids from a product ids list
     *
     * @param array $productIds
     *
     * @return array
     */
    public function getAvailableAttributeIdsToExport(array $productIds);

    /**
     * Get full products from product ids
     *
     * @param array $productIds
     * @param array $attributeIds
     *
     * @return array
     */
    public function getFullProducts(array $productIds, array $attributeIds = array());

    /**
     * Find all common attributes ids linked to a family
     * A list of product ids can be passed as parameter
     *
     * @param array $productIds
     *
     * @return mixed
     */
    public function findFamilyCommonAttributeIds(array $productIds);

    /**
     * Find all common attribute ids with values from a list of product ids
     *
     * @param array $productIds
     *
     * @return mixed
     */
    public function findValuesCommonAttributeIds(array $productIds);
}
